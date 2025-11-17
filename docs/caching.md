# Caching Strategy

- Product search: Redis, key=`search:{q}:{page}`, TTL=300s
- Permissions: Array driver (per-request)
- Low-stock list: Redis, key=`low_stock`, TTL=3600s
- PDF invoices: Stored in `storage/app/invoices/` (file cache)

No cache used in inventory/order transactions — ensures data integrity.