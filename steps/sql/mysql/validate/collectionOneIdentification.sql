SELECT
  COUNT(taxon_type.idno) AS "COUNT"
FROM ca_objects o
  JOIN ca_objects_x_vocabulary_terms identification ON o.object_id = identification.object_id
  JOIN ca_list_items taxon ON identification.item_id = taxon.item_id
  JOIN ca_list_items object_type ON o.type_id = object_type.item_id
  JOIN ca_list_items taxon_type ON taxon.type_id = taxon_type.item_id
  JOIN ca_relationship_types rel ON identification.type_id = rel.type_id
WHERE !o.deleted and !taxon.deleted AND taxon_type.idno = "collectionOneTaxon";