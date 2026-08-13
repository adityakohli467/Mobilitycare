-- Adds a per-product "primary breadcrumb category" so multi-category products
-- always show a fixed breadcrumb trail regardless of how the visitor arrived
-- (direct link, Google Ad, category browse, etc.). 0 = not set (auto-detect).
ALTER TABLE `oc_product`
	ADD COLUMN `primary_category_id` INT(11) NOT NULL DEFAULT 0;
