CREATE OR REPLACE VIEW collectionTwoCollectingTeam AS
	SELECT
		TRIM(REPLACE(REPLACE(vchrCollector, '<', ''), '>', '')) as verbatimCollectorList,
		TRIM(REPLACE(REPLACE(REPLACE(REPLACE(vchrCollector, ' & ', ';'), ', ', ';'), '<', ''), '>', '')) as collectorList,
             	dtDateCreated as siteVisitdtDateCreated,
		vchrWhoCreated as siteVisitvchrWhoCreated,
		dtDateLastUpdated as siteVisitdtDateLastUpdated,
		vchrWhoLastUpdated as siteVisitvchrWhoLastUpdated,
		intSiteVisitID as siteVisitID
	FROM tblSiteVisit;
