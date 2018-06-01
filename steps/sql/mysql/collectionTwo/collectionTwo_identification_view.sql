CREATE OR REPLACE VIEW collectionTwoIdentificationNotes AS
	SELECT
		n.intIntraCatID,
		c.vchrCategory,
		t.vchrNoteType,
		n.txtComments,
		n.txtNote
	FROM tblNote n
		JOIN tblNoteType t USING (intNoteTypeID)
		JOIN tblTraitCategory c ON t.intCategoryID = c.intTraitCategoryID
	WHERE vchrNoteType IN ('Identification', 'IoA key');
CREATE OR REPLACE VIEW collectionTwoIdentification
	AS
		SELECT
			intMaterialID,
			b.vchrEpithet,
			m.intBiotaID,
			vchrIDBy,
			DATE_FORMAT(dtIDDate, '%Y-%m-%d') AS dtIDDate,
			intIDRefID,
			vchrIDMethod,
			CASE
			WHEN vchrIDMethod IN ('Curate', 'curated')
				THEN 'curated'
			WHEN vchrIDMethod IN ('19940531', 'Brindabella', 'other', '-1', '')
				THEN NULL
			WHEN vchrIDMethod IN ('revised', 'Revision', 'revsion')
				THEN 'revised'
			ELSE NULL
			END                               AS identificationMethod,

			vchrIDAccuracy,
			vchrIDNameQual,
			vchrIDNotes,
			vchrIDRefPage,
			vchrRefCode,
			n.txtNote                         AS internalNote,
			ir.txtNote                        AS identificationReferences,
			m.dtDateCreated,
	                m.vchrWhoCreated,
	                m.dtDateLastUpdated,
			m.vchrWhoLastUpdated
		FROM tblMaterial m LEFT JOIN tblReference r ON m.intIDRefID = r.intRefID
			JOIN tblBiota b USING (intBiotaID)
			LEFT JOIN collectionTwoIdentificationNotes n
				ON n.intIntraCatID = m.intMaterialID AND n.vchrNoteType = 'Identification'
			LEFT JOIN collectionTwoIdentificationNotes ir ON n.intIntraCatID = m.intMaterialID AND n.vchrNoteType = 'IoA key';
