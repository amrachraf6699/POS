<?php

namespace Modules\Catalog\App\Domain\Csv\Data;

final class CatalogCsvImportError
{
    public function __construct(
        public readonly int $row,
        public readonly string $field,
        public readonly string $message,
    ) {}

    /** @return array{row: int, field: string, message: string} */
    public function toArray(): array
    {
        return ['row' => $this->row, 'field' => $this->field, 'message' => $this->message];
    }
}
