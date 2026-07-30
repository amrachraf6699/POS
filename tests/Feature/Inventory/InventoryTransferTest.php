<?php

namespace Tests\Feature\Inventory;

use Modules\Business\App\Domain\Settings\BusinessSettingsService;
use Modules\Business\App\Models\Branch;
use Modules\Catalog\App\Models\Category;
use Modules\Catalog\App\Models\Product;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Modules\Inventory\App\Actions\ApproveInventoryTransferAction;
use Modules\Inventory\App\Actions\CancelInventoryTransferAction;
use Modules\Inventory\App\Actions\CreateInventoryTransferAction;
use Modules\Inventory\App\Domain\Data\CreateInventoryTransferData;
use Modules\Inventory\App\Domain\Enums\InventoryTransferStatus;
use Modules\Inventory\App\Models\InventoryBalance;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class InventoryTransferTest extends TenantIsolationTestCase
{
    public function test_immediate_transfer_posts_paired_movements_balances_and_is_idempotent(): void
    {
        [$owner, $tenant, $source, $destination, $product] = $this->fixture();
        $balance = new InventoryBalance;
        $balance->forceFill(['branch_id' => $source->getKey(), 'product_id' => $product->getKey(), 'quantity_on_hand' => 8]);
        $balance->save();
        $data = new CreateInventoryTransferData((int) $source->getKey(), (int) $destination->getKey(), 'إعادة توزيع', [['product_id' => (int) $product->getKey(), 'quantity' => 3]], 'transfer-immediate');
        $result = app(CreateInventoryTransferAction::class)->execute($owner, $tenant, $data);
        $retry = app(CreateInventoryTransferAction::class)->execute($owner, $tenant, $data);

        $this->assertSame(InventoryTransferStatus::Posted, $result->transfer->status);
        $this->assertTrue($retry->wasIdempotent);
        $this->assertSame(5, InventoryBalance::query()->where('branch_id', $source->getKey())->where('product_id', $product->getKey())->value('quantity_on_hand'));
        $this->assertSame(3, InventoryBalance::query()->where('branch_id', $destination->getKey())->where('product_id', $product->getKey())->value('quantity_on_hand'));
        $this->assertDatabaseCount('inventory_movements', 2);
        $item = $result->transfer->fresh()->items()->firstOrFail();
        $this->assertNotNull($item->transfer_out_movement_id);
        $this->assertNotNull($item->transfer_in_movement_id);
    }

    public function test_pending_transfer_can_be_approved_or_cancelled_without_duplicate_movements(): void
    {
        [$owner, $tenant, $source, $destination, $product] = $this->fixture();
        app(BusinessSettingsService::class)->update(new \Modules\Business\App\Data\BusinessSettingsData(['transfer_requires_manager_approval' => true]));
        $balance = new InventoryBalance;
        $balance->forceFill(['branch_id' => $source->getKey(), 'product_id' => $product->getKey(), 'quantity_on_hand' => 4]);
        $balance->save();
        $pending = app(CreateInventoryTransferAction::class)->execute($owner, $tenant, new CreateInventoryTransferData((int) $source->getKey(), (int) $destination->getKey(), 'بانتظار اعتماد', [['product_id' => (int) $product->getKey(), 'quantity' => 2]], 'transfer-pending'))->transfer;
        $this->assertSame(InventoryTransferStatus::Pending, $pending->status);
        $this->assertDatabaseCount('inventory_movements', 0);
        $approved = app(ApproveInventoryTransferAction::class)->execute($owner, $tenant, $pending);
        $this->assertSame(InventoryTransferStatus::Posted, $approved->transfer->status);
        $this->assertTrue(app(ApproveInventoryTransferAction::class)->execute($owner, $tenant, $pending)->wasIdempotent);
        $cancelled = app(CreateInventoryTransferAction::class)->execute($owner, $tenant, new CreateInventoryTransferData((int) $source->getKey(), (int) $destination->getKey(), 'إلغاء', [['product_id' => (int) $product->getKey(), 'quantity' => 1]], 'transfer-cancel'))->transfer;
        app(CancelInventoryTransferAction::class)->execute($owner, $tenant, $cancelled, 'لم يعد مطلوباً');
        $this->assertSame(InventoryTransferStatus::Cancelled, $cancelled->fresh()->status);
        $this->assertDatabaseCount('inventory_movements', 2);
    }

    public function test_transfer_detail_page_renders_for_an_authorized_tenant_user(): void
    {
        [$owner, $tenant, $source, $destination, $product] = $this->fixture();
        $balance = new InventoryBalance;
        $balance->forceFill(['branch_id' => $source->getKey(), 'product_id' => $product->getKey(), 'quantity_on_hand' => 1]);
        $balance->save();
        $transfer = app(CreateInventoryTransferAction::class)->execute($owner, $tenant, new CreateInventoryTransferData(
            (int) $source->getKey(),
            (int) $destination->getKey(),
            'عرض التفاصيل',
            [['product_id' => (int) $product->getKey(), 'quantity' => 1]],
            'transfer-detail-page',
        ))->transfer;

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get(route('inventory.transfers.show', $transfer))
            ->assertOk()
            ->assertSee('تفاصيل التحويل')
            ->assertSee($product->name);
    }

    /** @return array{0: User, 1: Tenant, 2: Branch, 3: Branch, 4: Product} */
    private function fixture(): array
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        app(TenantContext::class)->set($tenant, $membership);
        $source = new Branch;
        $source->forceFill(['name' => 'القاهرة', 'code' => 'CAI', 'status' => Branch::STATUS_ACTIVE]);
        $source->save();
        $destination = new Branch;
        $destination->forceFill(['name' => 'الجيزة', 'code' => 'GIZ', 'status' => Branch::STATUS_ACTIVE]);
        $destination->save();
        $category = new Category;
        $category->forceFill(['name' => 'مشروبات']);
        $category->save();
        $tax = new TaxRate;
        $tax->forceFill(['name' => 'ضريبة', 'rate_basis_points' => 1400, 'effective_from' => today(), 'status' => TaxRate::STATUS_ACTIVE]);
        $tax->save();
        $product = new Product;
        $product->forceFill(['category_id' => $category->getKey(), 'tax_rate_id' => $tax->getKey(), 'name' => 'قهوة', 'selling_price_minor' => 100, 'track_inventory' => true, 'status' => Product::STATUS_ACTIVE]);
        $product->save();

        return [$owner, $tenant, $source, $destination, $product];
    }
}
