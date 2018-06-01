SELECT
    COUNT(ca_l_i.item_value) AS "COUNT"
FROM
    ca_places ca_p
LEFT JOIN ca_list_items ca_l_i ON
    ca_p.type_id = ca_l_i.item_id AND ca_l_i.parent_id IS NOT NULL
LEFT JOIN ca_lists ca_l ON
    ca_l.list_id = ca_l_i.list_id
WHERE
    ca_p.deleted = 0 AND ca_l_i.item_value = "collectionOneLocation"