<?php

namespace Modules\Catalog\App\Domain\Csv\Data;

final class CatalogCsvImportResult
{
    /** @param array<int, CatalogCsvImportError> $errors */
    public function __construct(
        public readonly int $totalRows,
        public readonly int $importedRows,
        public readonly array $errors,
    ) {}

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /** @return array{total_rows: int, imported_rows: int, errors: array<int, array{row: int, field: string, message: string}>} */
    public function toArray(): array
    {
        return [
            'total_rows' => $this->totalRows,
            'imported_rows' => $this->importedRows,
            'errors' => array_map(static fn (CatalogCsvImportError $error): array => $error->toArray(), $this->errors),
        ];
    }
}
