<?php

namespace App\Enums;

class ProcurementStatus
{
    const PENDING = 'pending';
    const BUDGET_PENDING = 'budget_pending';
    const APPROVED = 'approved';
    const COMPLETED = 'completed';
    const CANCELLED = 'cancelled';
    const CASH_IN_TRANSIT = 'cash_in_transit';
    const DELIVERY_PENDING = 'delivery_pending';
    const PENDING_ORDER_TO_SUPPLIER = 'pending_order_to_supplier';
    const ONGOING_DELIVERY = 'ongoing_delivery';
    const AWAITING_INVENTORY_CONFIRMATION = 'awaiting_inventory_confirmation';
}
