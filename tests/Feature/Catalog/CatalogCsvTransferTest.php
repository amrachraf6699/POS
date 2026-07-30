<?php

namespace Tests\Feature\Catalog;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\App\Actions\ImportProductsFromCsvAction;
use Modules\Catalog\App\Models\Category;
use Modules\Catalog\App\Models\Product;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\Membership;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class CatalogCsvTransferTest extends TenantIsolationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_authorized_user_can_partially_import_products_using_tenant_local_names(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        [$category, $taxRate] = $this->catalogSources($tenant, $membership, 'Beverages', 'VAT');
        $header = implode(',', ImportProductsFromCsvAction::HEADER);
        $csv = $header."\n"
            .'Cola,Beverages,VAT,COLA-1,100,Cold drink,125,250,1,3,0,inactive'."\n"
            .'Unknown,Missing,VAT,MISSING-1,101,,0,1,1,0,0,active'."\n"
            .'Duplicate one,Beverages,VAT,DUP-1,102,,0,1,1,0,0,active'."\n"
            .'Duplicate two,Beverages,VAT,DUP-1,103,,0,1,1,0,0,active'."\n";

        $response = $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post(route('catalog.products.import.store'), ['catalog_csv' => UploadedFile::fake()->createWithContent('products.csv', $csv)]);

        $response->assertRedirect(route('catalog.products.import.form'))
            ->assertSessionHas('catalog_import_result', function (array $result): bool {
                return $result['total_rows'] === 4
                    && $result['imported_rows'] === 1
                    && collect($result['errors'])->contains(fn (array $error): bool => $error['row'] === 4 && $error['field'] === 'sku')
                    && collect($result['errors'])->contains(fn (array $error): bool => $error['row'] === 5 && $error['field'] === 'sku');
            });
        $this->assertDatabaseHas('products', [
            'tenant_id' => $tenant->getKey(),
            'category_id' => $category->getKey(),
            'tax_rate_id' => $taxRate->getKey(),
            'sku' => 'COLA-1',
            'selling_price_minor' => 250,
            'track_inventory' => 1,
            'low_stock_threshold' => 3,
            'allow_negative_stock' => 0,
            'status' => Product::STATUS_INACTIVE,
        ]);
        $this->assertDatabaseMissing('products', ['tenant_id' => $tenant->getKey(), 'sku' => 'MISSING-1']);
    }

    public function test_import_rejects_malformed_or_empty_files_and_soft_deleted_identifiers(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        [$category, $taxRate] = $this->catalogSources($tenant, $membership, 'Food', 'VAT');
        $header = implode(',', ImportProductsFromCsvAction::HEADER);
        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post(route('catalog.products.import.store'), ['catalog_csv' => UploadedFile::fake()->createWithContent('products.csv', "wrong,header\n")])
            ->assertSessionHas('catalog_import_result.errors.0.field', 'header');
        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post(route('catalog.products.import.store'), ['catalog_csv' => UploadedFile::fake()->createWithContent('products.csv', $header."\n")])
            ->assertSessionHas('catalog_import_result.errors.0.field', 'file');
        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post(route('catalog.products.import.store'), ['catalog_csv' => UploadedFile::fake()->createWithContent('products.csv', '')])
            ->assertSessionHas('catalog_import_result.errors.0.field', 'file');

        /** @var Product $deleted */
        $deleted = Product::query()->create([
            'category_id' => $category->getKey(), 'tax_rate_id' => $taxRate->getKey(), 'name' => 'Old food', 'sku' => 'REMOVED-1',
            'cost_price_minor' => 0, 'selling_price_minor' => 100, 'track_inventory' => true, 'low_stock_threshold' => 0,
            'allow_negative_stock' => false, 'status' => Product::STATUS_ACTIVE,
        ]);
        $deleted->delete();
        $csv = $header."\n".'New food,Food,VAT,REMOVED-1,999,,0,100,1,0,0,active'."\n";

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post(route('catalog.products.import.store'), ['catalog_csv' => UploadedFile::fake()->createWithContent('products.csv', $csv)])
            ->assertSessionHas('catalog_import_result', fn (array $result): bool => $result['imported_rows'] === 0 && $result['errors'][0]['field'] === 'sku');
    }

    public function test_import_does_not_resolve_another_tenants_sources_or_create_products_there(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        $this->catalogSources($tenant, $membership, 'Local category', 'Local VAT');
        [$otherOwner, $otherTenant, $otherMembership] = $this->makeMembership();
        $this->catalogSources($otherTenant, $otherMembership, 'Other category', 'Other VAT');
        $header = implode(',', ImportProductsFromCsvAction::HEADER);
        $csv = $header."\n".'Leaked,Other category,Other VAT,LEAK-1,123,,0,100,1,0,0,active'."\n";

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post(route('catalog.products.import.store'), ['catalog_csv' => UploadedFile::fake()->createWithContent('products.csv', $csv)])
            ->assertSessionHas('catalog_import_result', function (array $result): bool {
                return $result['imported_rows'] === 0
                    && collect($result['errors'])->contains(fn (array $error): bool => $error['field'] === 'category_name')
                    && collect($result['errors'])->contains(fn (array $error): bool => $error['field'] === 'tax_rate_name');
            });
        $this->assertDatabaseMissing('products', ['tenant_id' => $tenant->getKey(), 'sku' => 'LEAK-1']);
        $this->assertDatabaseMissing('products', ['tenant_id' => $otherTenant->getKey(), 'sku' => 'LEAK-1']);
        $this->assertTrue($otherOwner->isActive());
    }

    public function test_batch_write_failure_rolls_back_that_batch_but_later_batches_continue(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        $this->catalogSources($tenant, $membership, 'Stock', 'VAT');
        DB::unprepared("CREATE TRIGGER reject_catalog_csv BEFORE INSERT ON products WHEN NEW.sku = 'FAIL-100' BEGIN SELECT RAISE(ABORT, 'forced write failure'); END;");
        $rows = [];
        for ($number = 1; $number <= 101; $number++) {
            $sku = $number === 100 ? 'FAIL-100' : "SKU-{$number}";
            $rows[] = "Product {$number},Stock,VAT,{$sku},BAR-{$number},,0,100,1,0,0,active";
        }
        $csv = implode(',', ImportProductsFromCsvAction::HEADER)."\n".implode("\n", $rows)."\n";

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post(route('catalog.products.import.store'), ['catalog_csv' => UploadedFile::fake()->createWithContent('products.csv', $csv)])
            ->assertSessionHas('catalog_import_result', fn (array $result): bool => $result['imported_rows'] === 1 && collect($result['errors'])->contains(fn (array $error): bool => $error['row'] === 2));
        $this->assertDatabaseMissing('products', ['tenant_id' => $tenant->getKey(), 'sku' => 'SKU-1']);
        $this->assertDatabaseHas('products', ['tenant_id' => $tenant->getKey(), 'sku' => 'SKU-101']);
    }

    public function test_export_is_permission_gated_tenant_scoped_and_formula_safe(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        [$category, $taxRate] = $this->catalogSources($tenant, $membership, 'Export category', 'VAT');
        Product::query()->create(['category_id' => $category->getKey(), 'tax_rate_id' => $taxRate->getKey(), 'name' => '=Formula', 'sku' => 'EXPORT-1', 'cost_price_minor' => 20, 'selling_price_minor' => 50, 'track_inventory' => true, 'low_stock_threshold' => 1, 'allow_negative_stock' => false, 'status' => Product::STATUS_INACTIVE]);
        [$otherOwner, $otherTenant, $otherMembership] = $this->makeMembership();
        [$otherCategory, $otherTaxRate] = $this->catalogSources($otherTenant, $otherMembership, 'Other export category', 'VAT');
        Product::query()->create(['category_id' => $otherCategory->getKey(), 'tax_rate_id' => $otherTaxRate->getKey(), 'name' => 'Other product', 'sku' => 'OTHER-EXPORT', 'cost_price_minor' => 20, 'selling_price_minor' => 50, 'track_inventory' => true, 'low_stock_threshold' => 1, 'allow_negative_stock' => false, 'status' => Product::STATUS_ACTIVE]);

        $response = $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])->get(route('catalog.products.export'));

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF".implode(',', ImportProductsFromCsvAction::HEADER), $content);
        $this->assertStringContainsString("\t=Formula", $content);
        $this->assertStringContainsString('inactive', $content);
        $this->assertStringNotContainsString('OTHER-EXPORT', $content);

        [$cashier, $cashierTenant] = $this->makeMembership(Membership::ROLE_CASHIER);
        $this->actingAs($cashier)->withSession(['current_tenant_id' => $cashierTenant->getKey()])->get(route('catalog.products.import.form'))->assertForbidden();
        $this->actingAs($cashier)->withSession(['current_tenant_id' => $cashierTenant->getKey()])->get(route('catalog.products.import.sample'))->assertForbidden();
        $this->actingAs($cashier)->withSession(['current_tenant_id' => $cashierTenant->getKey()])->get(route('catalog.products.export'))->assertForbidden();
    }

    public function test_product_list_only_shows_csv_actions_when_authorised(): void
    {
        [$owner, $tenant] = $this->makeMembership();
        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])->get(route('catalog.products.index'))
            ->assertOk()->assertSee(route('catalog.products.import.form'))->assertSee(route('catalog.products.export'));
        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])->get(route('catalog.products.import.form'))
            ->assertOk()->assertSee('تنزيل نموذج CSV')->assertDontSee(route('catalog.products.import.sample'));

        [$cashier, $cashierTenant] = $this->makeMembership(Membership::ROLE_CASHIER);
        $this->actingAs($cashier)->withSession(['current_tenant_id' => $cashierTenant->getKey()])->get(route('catalog.products.index'))
            ->assertOk()->assertDontSee(route('catalog.products.import.form'))->assertDontSee(route('catalog.products.export'));
    }

    public function test_authorized_user_can_download_a_tenant_valid_product_import_sample(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        $this->catalogSources($tenant, $membership, 'Sample category', 'Sample VAT');

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])->get(route('catalog.products.import.form'))
            ->assertOk()
            ->assertSee(route('catalog.products.import.sample'))
            ->assertDontSee('تنسيق الملف المعتمد');

        $response = $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])->get(route('catalog.products.import.sample'));

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF".implode(',', ImportProductsFromCsvAction::HEADER), $content);
        $this->assertStringContainsString('"Sample category","Sample VAT"', $content);
        $this->assertStringContainsString('active', $content);
    }

    /** @return array{0: Category, 1: TaxRate} */
    private function catalogSources($tenant, Membership $membership, string $categoryName, string $taxRateName): array
    {
        app(TenantContext::class)->set($tenant, $membership);
        /** @var Category $category */
        $category = Category::query()->create(['name' => $categoryName]);
        /** @var TaxRate $taxRate */
        $taxRate = TaxRate::query()->create(['name' => $taxRateName, 'rate_basis_points' => 1400, 'effective_from' => today(), 'status' => TaxRate::STATUS_ACTIVE]);

        return [$category, $taxRate];
    }
}
