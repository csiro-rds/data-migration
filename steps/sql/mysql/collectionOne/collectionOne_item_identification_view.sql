CREATE OR REPLACE VIEW CMS_ITEM_IDENTIFICATION_W_CU_AND_ITEM_IDNO_V AS
	SELECT
		II.LEGACYID_IDENT,
		II.LEGACYID_CIIDENT,
		II.LEGACYID_CI,
		II.TYPEOFTYPE,
		II.TYPEQUALIFIER,
		II.TYPESTATUS,
		II.CREATEDBY,
		II.CREATEDATE,
		II.UPDATEDBY,
		II.UPDATEDATE,
		CI.CA_IDNO,
		CU.EXCHANGEUNITID
	FROM CMS_ITEM_IDENTIFICATION_V II
		JOIN CMS_COLLECTION_ITEM_W_LABEL_V CI USING (LEGACYID_CI)
		JOIN cms_identification_view i USING (LEGACYID_IDENT)
		JOIN CMS_COLLECTION_UNIT_V CU ON (i.LEGACYID_CU = CU.LEGACYID_CU);