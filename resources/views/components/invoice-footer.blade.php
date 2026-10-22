@include('components.attachments-widget', ['modelType' => 'App\\Models\\Invoice', 'modelId' => $invoice->id])

@if(!in_array($invoice->type, ['sale_return', 'purchase_return'], true))
    @include('components.installments-widget', ['invoice' => $invoice])
@endif
