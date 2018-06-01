# The following SQL is generated using this query
# SET SESSION GROUP_CONCAT_MAX_LEN = 1024 * 1024 * 1024;
# SELECT GROUP_CONCAT(CONCAT('UPDATE ', TABLE_NAME, ' target
# 	JOIN ca_list_items t ON target.type_id = t.item_id
# 	JOIN ca_lists tl ON t.list_id = tl.list_id
# 	JOIN ca_lists tn ON (REPLACE(tl.list_code, \'types\', \'sources\') = tn.list_code)
# 	JOIN ca_list_items s ON (tn.list_id = s.list_id AND left(s.idno, 3) = left(t.idno, 3))
# SET target.source_id = s.item_id
# WHERE
# 	tl.list_code LIKE \'%_types\'
# 	AND
# 	tl.list_code NOT LIKE \'%_label_types\'
# 	AND t.idno NOT LIKE \'Root node%\'
# 	AND s.idno NOT LIKE \'Root node%\' AND target.source_id IS NULL;') SEPARATOR "\n") AS q
# FROM information_schema.COLUMNS
# WHERE TABLE_SCHEMA = (SELECT DATABASE()) AND COLUMN_NAME = 'source_id';

# This identifies all tables that have a source_id, the associated type and source
# lists for the tables and matches types with sources based on the first 3 characters
#
# So occurrence type collectionTwoCollectingEvent => source CollectionTwo

UPDATE ca_collections target
	JOIN ca_list_items t ON target.type_id = t.item_id
	JOIN ca_lists tl ON t.list_id = tl.list_id
	JOIN ca_lists tn ON (REPLACE(tl.list_code, 'types', 'sources') = tn.list_code)
	JOIN ca_list_items s ON (tn.list_id = s.list_id AND left(s.idno, 3) = left(t.idno, 3))
SET target.source_id = s.item_id
WHERE
	tl.list_code LIKE '%_types'
	AND
	tl.list_code NOT LIKE '%_label_types'
	AND t.idno NOT LIKE 'Root node%'
	AND s.idno NOT LIKE 'Root node%' AND target.source_id IS NULL;
UPDATE ca_entities target
	JOIN ca_list_items t ON target.type_id = t.item_id
	JOIN ca_lists tl ON t.list_id = tl.list_id
	JOIN ca_lists tn ON (REPLACE(tl.list_code, 'types', 'sources') = tn.list_code)
	JOIN ca_list_items s ON (tn.list_id = s.list_id AND left(s.idno, 3) = left(t.idno, 3))
SET target.source_id = s.item_id
WHERE
	tl.list_code LIKE '%_types'
	AND
	tl.list_code NOT LIKE '%_label_types'
	AND t.idno NOT LIKE 'Root node%'
	AND s.idno NOT LIKE 'Root node%' AND target.source_id IS NULL;
UPDATE ca_list_items target
	JOIN ca_list_items t ON target.type_id = t.item_id
	JOIN ca_lists tl ON t.list_id = tl.list_id
	JOIN ca_lists tn ON (REPLACE(tl.list_code, 'types', 'sources') = tn.list_code)
	JOIN ca_list_items s ON (tn.list_id = s.list_id AND left(s.idno, 3) = left(t.idno, 3))
SET target.source_id = s.item_id
WHERE
	tl.list_code LIKE '%_types'
	AND
	tl.list_code NOT LIKE '%_label_types'
	AND t.idno NOT LIKE 'Root node%'
	AND s.idno NOT LIKE 'Root node%' AND target.source_id IS NULL;
UPDATE ca_loans target
	JOIN ca_list_items t ON target.type_id = t.item_id
	JOIN ca_lists tl ON t.list_id = tl.list_id
	JOIN ca_lists tn ON (REPLACE(tl.list_code, 'types', 'sources') = tn.list_code)
	JOIN ca_list_items s ON (tn.list_id = s.list_id AND left(s.idno, 3) = left(t.idno, 3))
SET target.source_id = s.item_id
WHERE
	tl.list_code LIKE '%_types'
	AND
	tl.list_code NOT LIKE '%_label_types'
	AND t.idno NOT LIKE 'Root node%'
	AND s.idno NOT LIKE 'Root node%' AND target.source_id IS NULL;
UPDATE ca_movements target
	JOIN ca_list_items t ON target.type_id = t.item_id
	JOIN ca_lists tl ON t.list_id = tl.list_id
	JOIN ca_lists tn ON (REPLACE(tl.list_code, 'types', 'sources') = tn.list_code)
	JOIN ca_list_items s ON (tn.list_id = s.list_id AND left(s.idno, 3) = left(t.idno, 3))
SET target.source_id = s.item_id
WHERE
	tl.list_code LIKE '%_types'
	AND
	tl.list_code NOT LIKE '%_label_types'
	AND t.idno NOT LIKE 'Root node%'
	AND s.idno NOT LIKE 'Root node%' AND target.source_id IS NULL;
UPDATE ca_object_lots target
	JOIN ca_list_items t ON target.type_id = t.item_id
	JOIN ca_lists tl ON t.list_id = tl.list_id
	JOIN ca_lists tn ON (REPLACE(tl.list_code, 'types', 'sources') = tn.list_code)
	JOIN ca_list_items s ON (tn.list_id = s.list_id AND left(s.idno, 3) = left(t.idno, 3))
SET target.source_id = s.item_id
WHERE
	tl.list_code LIKE '%_types'
	AND
	tl.list_code NOT LIKE '%_label_types'
	AND t.idno NOT LIKE 'Root node%'
	AND s.idno NOT LIKE 'Root node%' AND target.source_id IS NULL;
UPDATE ca_object_representations target
	JOIN ca_list_items t ON target.type_id = t.item_id
	JOIN ca_lists tl ON t.list_id = tl.list_id
	JOIN ca_lists tn ON (REPLACE(tl.list_code, 'types', 'sources') = tn.list_code)
	JOIN ca_list_items s ON (tn.list_id = s.list_id AND left(s.idno, 3) = left(t.idno, 3))
SET target.source_id = s.item_id
WHERE
	tl.list_code LIKE '%_types'
	AND
	tl.list_code NOT LIKE '%_label_types'
	AND t.idno NOT LIKE 'Root node%'
	AND s.idno NOT LIKE 'Root node%' AND target.source_id IS NULL;
UPDATE ca_objects target
	JOIN ca_list_items t ON target.type_id = t.item_id
	JOIN ca_lists tl ON t.list_id = tl.list_id
	JOIN ca_lists tn ON (REPLACE(tl.list_code, 'types', 'sources') = tn.list_code)
	JOIN ca_list_items s ON (tn.list_id = s.list_id AND left(s.idno, 3) = left(t.idno, 3))
SET target.source_id = s.item_id
WHERE
	tl.list_code LIKE '%_types'
	AND
	tl.list_code NOT LIKE '%_label_types'
	AND t.idno NOT LIKE 'Root node%'
	AND s.idno NOT LIKE 'Root node%' AND target.source_id IS NULL;
UPDATE ca_occurrences target
	JOIN ca_list_items t ON target.type_id = t.item_id
	JOIN ca_lists tl ON t.list_id = tl.list_id
	JOIN ca_lists tn ON (REPLACE(tl.list_code, 'types', 'sources') = tn.list_code)
	JOIN ca_list_items s ON (tn.list_id = s.list_id AND left(s.idno, 3) = left(t.idno, 3))
SET target.source_id = s.item_id
WHERE
	tl.list_code LIKE '%_types'
	AND
	tl.list_code NOT LIKE '%_label_types'
	AND t.idno NOT LIKE 'Root node%'
	AND s.idno NOT LIKE 'Root node%' AND target.source_id IS NULL;
UPDATE ca_places target
	JOIN ca_list_items t ON target.type_id = t.item_id
	JOIN ca_lists tl ON t.list_id = tl.list_id
	JOIN ca_lists tn ON (REPLACE(tl.list_code, 'types', 'sources') = tn.list_code)
	JOIN ca_list_items s ON (tn.list_id = s.list_id AND left(s.idno, 3) = left(t.idno, 3))
SET target.source_id = s.item_id
WHERE
	tl.list_code LIKE '%_types'
	AND
	tl.list_code NOT LIKE '%_label_types'
	AND t.idno NOT LIKE 'Root node%'
	AND s.idno NOT LIKE 'Root node%' AND target.source_id IS NULL;
UPDATE ca_storage_locations target
	JOIN ca_list_items t ON target.type_id = t.item_id
	JOIN ca_lists tl ON t.list_id = tl.list_id
	JOIN ca_lists tn ON (REPLACE(tl.list_code, 'types', 'sources') = tn.list_code)
	JOIN ca_list_items s ON (tn.list_id = s.list_id AND left(s.idno, 3) = left(t.idno, 3))
SET target.source_id = s.item_id
WHERE
	tl.list_code LIKE '%_types'
	AND
	tl.list_code NOT LIKE '%_label_types'
	AND t.idno NOT LIKE 'Root node%'
	AND s.idno NOT LIKE 'Root node%' AND target.source_id IS NULL;
UPDATE ca_tours target
	JOIN ca_list_items t ON target.type_id = t.item_id
	JOIN ca_lists tl ON t.list_id = tl.list_id
	JOIN ca_lists tn ON (REPLACE(tl.list_code, 'types', 'sources') = tn.list_code)
	JOIN ca_list_items s ON (tn.list_id = s.list_id AND left(s.idno, 3) = left(t.idno, 3))
SET target.source_id = s.item_id
WHERE
	tl.list_code LIKE '%_types'
	AND
	tl.list_code NOT LIKE '%_label_types'
	AND t.idno NOT LIKE 'Root node%'
	AND s.idno NOT LIKE 'Root node%' AND target.source_id IS NULL;
