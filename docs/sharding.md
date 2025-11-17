# Database Sharding Strategy

For scalability, we recommend **sharding by vendor_id**.

- Shard key: `vendor_id`
- Each vendor’s products/orders stored in separate database
- Admin queries aggregate across shards (using Laravel Octane + async)
- Shard mapping stored in central `shard_map` table
- Implemented via dynamic DB connections: `DB::connection('shard_'.$vendorId)`