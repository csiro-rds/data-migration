-- Step                : write to [tblCharacterState]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblCharacterState;
CREATE TABLE tblCharacterState(
	intCharacterStateID INT
	, intCharacterID INT
	, vchrName TEXT
	, intOrder INT
	, bitDefault BOOLEAN
	, intOriginalDeltaNumber INT
	, GUID VARCHAR(36)
	, dtDateCreated DATETIME
	, vchrWhoCreated VARCHAR(50)
	, dtDateLastUpdated DATETIME
	, vchrWhoLastUpdated VARCHAR(50)
)
;

-- Step                : write to [tblUser]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblUser;
CREATE TABLE tblUser(
vchrName VARCHAR(128)
, intGroupID INT
, vchrFullName VARCHAR(100)
, vchrDescription VARCHAR(200)
, vchrNotes TEXT
, intUserID INT
, GUID VARCHAR(36)
, bitCanCreateUsers BOOLEAN
)
;

-- Step                : write to [tblSite]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblSite;
CREATE TABLE tblSite(
intSiteID INT
, vchrSiteName VARCHAR(100)
, intPoliticalRegionID INT
, intSiteGroupID INT
, tintLocalType INT
, vchrLocal VARCHAR(255)
, vchrDistanceFromPlace VARCHAR(50)
, vchrDirFromPlace VARCHAR(10)
, vchrInformalLocal VARCHAR(255)
, tintPosCoordinates INT
, tintPosAreaType INT
, fltPosY1 DOUBLE
, fltPosX1 DOUBLE
, fltPosX2 DOUBLE
, fltPosY2 DOUBLE
, tintPosXYDisplayFormat INT
, vchrPosSource VARCHAR(50)
, vchrPosError VARCHAR(20)
, vchrPosWho VARCHAR(20)
, vchrPosDate VARCHAR(20)
, vchrPosOriginal VARCHAR(20)
, vchrPosUTMSource VARCHAR(255)
, vchrPosUTMMapProj VARCHAR(255)
, vchrPosUTMMapName VARCHAR(255)
, vchrPosUTMMapVer VARCHAR(255)
, tintElevType INT
, fltElevUpper DOUBLE
, fltElevLower DOUBLE
, fltElevDepth DOUBLE
, vchrElevUnits VARCHAR(20)
, vchrElevSource VARCHAR(50)
, vchrElevError VARCHAR(20)
, vchrGeoEra VARCHAR(50)
, vchrGeoState VARCHAR(50)
, vchrGeoPlate VARCHAR(50)
, vchrGeoFormation VARCHAR(50)
, vchrGeoMember VARCHAR(50)
, vchrGeoBed VARCHAR(50)
, vchrGeoName VARCHAR(50)
, vchrGeoAgeBottom VARCHAR(50)
, vchrGeoAgeTop VARCHAR(50)
, vchrGeoNotes VARCHAR(255)
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, intOrder INT
, tintTemplate INT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblLabelSet]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblLabelSet;
CREATE TABLE tblLabelSet(
intLabelSetID INT
, vchrUsername VARCHAR(50)
, vchrLabelSetName VARCHAR(200)
, GUID VARCHAR(36)
, txtDelimitedFields MEDIUMTEXT
)
;

-- Step                : write to [tblEcologyBiotaRegion]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblEcologyBiotaRegion;
CREATE TABLE tblEcologyBiotaRegion(
intBiotaID INT
, intRegionID INT
, intFamilyRichness INT
, intSubfamilyRichness INT
, intGenusRichness INT
, intSpeciesRichness INT
, intEndemismScore INT
, intTaxonomicDiversity INT
, dtDateLastUpdate DATETIME
)
;

-- Step                : write to [tblSiteGroup]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblSiteGroup;
CREATE TABLE tblSiteGroup(
intSiteGroupID INT
, sintParentType INT
, intParentID INT
, intPoliticalRegionID INT
, vchrSiteGroupName VARCHAR(255)
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, vchrParentage VARCHAR(255)
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblAssociate]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblAssociate;
CREATE TABLE tblAssociate(
intAssociateID INT
, intFromIntraCatID INT
, intFromCatID INT
, intToIntraCatID INT
, intToCatID INT
, txtAssocDescription MEDIUMTEXT
, vchrRelationFromTo VARCHAR(50)
, vchrRelationToFrom VARCHAR(50)
, intPoliticalRegionID INT
, vchrSource VARCHAR(50)
, intRefID INT
, vchrRefPage VARCHAR(255)
, bitUncertain BOOLEAN
, txtNotes MEDIUMTEXT
, GUID VARCHAR(36)
, intBasedOnID INT
)
;

-- Step                : write to [tblMorphologyProjectView]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMorphologyProjectView;
CREATE TABLE tblMorphologyProjectView(
intMorphologyProjectViewID INT
, intMorphologyProjectID INT
, intMorphologyViewID INT
)
;

-- Step                : write to [tblSiteVisit]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblSiteVisit;
CREATE TABLE tblSiteVisit(
intSiteVisitID INT
, intSiteID INT
, vchrSiteVisitName VARCHAR(255)
, vchrFieldNumber VARCHAR(50)
, vchrCollector VARCHAR(255)
, tintDateType INT
, intDateStart INT
, intDateEnd INT
, intTimeStart INT
, intTimeEnd INT
, vchrCasualTime VARCHAR(255)
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, tintTemplate INT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblALN]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblALN;
CREATE TABLE tblALN(
intBiotaID INT
, intRefID INT
, vchrRefPage VARCHAR(50)
, txtRefQual MEDIUMTEXT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblJournal]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblJournal;
CREATE TABLE tblJournal(
intJournalID INT
, vchrAbbrevName VARCHAR(100)
, vchrAbbrevName2 VARCHAR(100)
, vchrAlias VARCHAR(100)
, vchrFullName TEXT
, txtNotes MEDIUMTEXT
, GUID VARCHAR(36)
, vchrWhoCreated VARCHAR(50)
, dtDateCreated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, dtDateLastUpdated DATETIME
)
;

-- Step                : write to [tblMorphologyCell]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMorphologyCell;
CREATE TABLE tblMorphologyCell(
intMorphologyCellID INT
, intCharacterID INT
, intMorphologyViewID INT
, intMorphologyEntityID INT
, txtValue MEDIUMTEXT
, intFlags INT
, intInsertedWithProjectID INT
, GUID VARCHAR(36)
)
;

-- Step                : write to [dtproperties]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS dtproperties;
CREATE TABLE dtproperties(
id INT
, objectid INT
, property VARCHAR(64)
, value VARCHAR(255)
, lvalue LONGBLOB
, version INT
, uvalue VARCHAR(255)
)
;

-- Step                : write to [tblMorphologyViewGroup]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMorphologyViewGroup;
CREATE TABLE tblMorphologyViewGroup(
intMorphologyViewGroupID INT
, intParentGroupID INT
, vchrName VARCHAR(255)
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblReport]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblReport;
CREATE TABLE tblReport(
intReportID INT
, vchrName VARCHAR(100)
, vchrOwner VARCHAR(50)
, txtReportXML MEDIUMTEXT
, GUID VARCHAR(36)
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, vchrContext VARCHAR(50)
)
;

-- Step                : write to [tblTypeSpecimen]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblTypeSpecimen;
CREATE TABLE tblTypeSpecimen(
cntTypeSpecimenID INT
, nlMaterialID INT
, tTypeStatus VARCHAR(50)
, tGenus VARCHAR(30)
, tSpecies VARCHAR(30)
, tAuthor VARCHAR(50)
, niYear INT
, lChangedComb BOOLEAN
, tDesignator VARCHAR(50)
, tDesignationYear VARCHAR(50)
, nlDesignationRefID VARCHAR(50)
)
;

-- Step                : write to [tblEcologyBiota]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblEcologyBiota;
CREATE TABLE tblEcologyBiota(
intBiotaID INT
, intSpecimenCount INT
, decDistributionSpread DOUBLE
, intRegionCount INT
, intEndemismScore INT
, dtLastUpdate DATETIME
)
;

-- Step                : write to [tblUserPermission]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblUserPermission;
CREATE TABLE tblUserPermission(
intUserID INT
, intBiotaID INT
, intPermMask1 INT
, intPermMask2 INT
, GUID VARCHAR(36)
, intGroupID INT
)
;

-- Step                : write to [tblBiota]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblBiota;
CREATE TABLE tblBiota(
intBiotaID INT
, intParentID INT
, vchrEpithet VARCHAR(200)
, vchrYearOfPub VARCHAR(50)
, vchrAuthor VARCHAR(100)
, vchrNameQualifier VARCHAR(15)
, chrElemType VARCHAR(5)
, vchrRank VARCHAR(50)
, intOrder INT
, vchrParentage VARCHAR(255)
, bitChangedComb BOOLEAN
, bitShadowed BOOLEAN
, bitUnplaced BOOLEAN
, bitUnverified BOOLEAN
, bitAvailableName BOOLEAN
, bitLiteratureName BOOLEAN
, vchrFullName VARCHAR(255)
, chrKingdomCode VARCHAR(2)
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, txtDistQual MEDIUMTEXT
, GUID VARCHAR(36)
, intImportedWithProjectID INT
, vchrAvailableNameStatus VARCHAR(255)
, vchrParentKingdom VARCHAR(200)
, vchrParentPhylum VARCHAR(200)
, vchrParentClass VARCHAR(200)
, vchrParentOrder VARCHAR(200)
, vchrParentFamily VARCHAR(200)
, vchrParentGenus VARCHAR(200)
, vchrParentSpecies VARCHAR(200)
, vchrParentSubspecies VARCHAR(200)
)
;

-- Step                : write to [tblGANIncludedSpecies]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblGANIncludedSpecies;
CREATE TABLE tblGANIncludedSpecies(
intGANISID INT
, intBiotaID INT
, GUID VARCHAR(36)
, vchrIncludedSpecies VARCHAR(255)
)
;

-- Step                : write to [tblNoteType]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblNoteType;
CREATE TABLE tblNoteType(
intNoteTypeID INT
, vchrNoteType VARCHAR(255)
, intCategoryID INT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblSetting]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblSetting;
CREATE TABLE tblSetting(
vchrName VARCHAR(255)
, vchrValue VARCHAR(255)
, vchrDescription VARCHAR(255)
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblAPPDMaterialBiota]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblAPPDMaterialBiota;
CREATE TABLE tblAPPDMaterialBiota(
intMaterialBiotaId INT
, vchrAuthority VARCHAR(100)
, intMaterialAssocBiotaId INT
, vchrOrder VARCHAR(200)
, intOrderBiotaId INT
, vchrSubOrder VARCHAR(200)
, intSubOrderBiotaId INT
, vchrInfraOrder VARCHAR(200)
, intInfraOrderBiotaId INT
, vchrSuperFamily VARCHAR(200)
, intSuperFamilyBiotaId INT
, vchrFamily VARCHAR(200)
, intFamilyBiotaId INT
, vchrSubFamily VARCHAR(200)
, intSubFamilyBiotaId INT
, vchrSuperTribe VARCHAR(200)
, intSuperTribeBiotaId INT
, vchrTribe VARCHAR(200)
, intTribeBiotaId INT
, vchrSubTribe VARCHAR(200)
, intSubTribeBiotaId INT
, vchrGenus VARCHAR(200)
, intGenusBiotaId INT
, vchrSubGenus VARCHAR(200)
, intSubGenusBiotaId INT
, vchrSection VARCHAR(200)
, intSectionBiotaId INT
, vchrSubSection VARCHAR(200)
, intSubSectionBiotaId INT
, vchrSeries VARCHAR(200)
, intSeriesBiotaId INT
, vchrSubSeries VARCHAR(200)
, intSubSeriesBiotaId INT
, vchrSpeciesGroup VARCHAR(200)
, intSpeciesGroupBiotaId INT
, vchrSpecies VARCHAR(200)
, intSpeciesBiotaId INT
, vchrSubSpecies VARCHAR(200)
, intSubSpeciesBiotaId INT
, vchrVariety VARCHAR(200)
, intVarietyBiotaId INT
, vchrSubVariety VARCHAR(200)
, intSubVarietyBiotaId INT
, vchrForm VARCHAR(200)
, intFormBiotaId INT
, vchrSubForm VARCHAR(200)
, intSubFormBiotaId INT
)
;

-- Step                : write to [tblSANTypeData]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblSANTypeData;
CREATE TABLE tblSANTypeData(
intSANTypeDataID INT
, intBiotaID INT
, vchrType VARCHAR(50)
, vchrMuseum VARCHAR(255)
, vchrAccessionNum VARCHAR(50)
, vchrMaterial VARCHAR(50)
, vchrLocality VARCHAR(255)
, bitIDConfirmed BOOLEAN
, GUID VARCHAR(36)
, intMaterialID INT
)
;

-- Step                : write to [r3dm0v3_sql]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS r3dm0v3_sql;
CREATE TABLE r3dm0v3_sql(
tmp1 TEXT
, tmp2 TEXT
)
;

-- Step                : write to [tblMorphologyCellNumeric]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMorphologyCellNumeric;
CREATE TABLE tblMorphologyCellNumeric(
intMorphologyCellID INT
, intCharacterNumericID INT
, numStartRange BIGINT
, numEndRange BIGINT
)
;

-- Step                : write to [tblCharacterGroup]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblCharacterGroup;
CREATE TABLE tblCharacterGroup(
intCharacterGroupID INT
, intParentID INT
, vchrName VARCHAR(255)
, intImportedWithProjectID INT
, vchrParentage TEXT
, GUID VARCHAR(36)
, vchrWhoCreated VARCHAR(50)
, dtDateCreated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, dtDateLastUpdated DATETIME
)
;

-- Step                : write to [tblTrap]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblTrap;
CREATE TABLE tblTrap(
intTrapID INT
, intSiteID INT
, vchrTrapName VARCHAR(100)
, vchrTrapType VARCHAR(50)
, vchrDescription VARCHAR(255)
, vchrWhoCreated VARCHAR(50)
, dtDateCreated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, dtDateLastUpdated DATETIME
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblCommonName]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblCommonName;
CREATE TABLE tblCommonName(
intCommonNameID INT
, intBiotaID INT
, vchrCommonName VARCHAR(100)
, vchrRefCode VARCHAR(50)
, vchrRefPage VARCHAR(50)
, txtNotes MEDIUMTEXT
, intRefID INT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblMorphologyProjectCharacter]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMorphologyProjectCharacter;
CREATE TABLE tblMorphologyProjectCharacter(
intMorphologyProjectCharacterID INT
, intMorphologyProjectID INT
, intCharacterID INT
, intOriginalDeltaNumber INT
, intOrder INT
, bitMandatory BOOLEAN
, bitHidden BOOLEAN
)
;

-- Step                : write to [tblPublication]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblPublication;
CREATE TABLE tblPublication(
intPublicationID INT
, vchrName VARCHAR(255)
, txtXMLData MEDIUMTEXT
, GUID VARCHAR(36)
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
)
;

-- Step                : write to [tblCharacterNumeric]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblCharacterNumeric;
CREATE TABLE tblCharacterNumeric(
intCharacterNumericID INT
, intCharacterID INT
, vchrName VARCHAR(255)
, tintType INT
, numMaximum BIGINT
, numMinimum BIGINT
, vchrUnits VARCHAR(255)
, intOrder INT
, vchrDescription TEXT
, bitDefault BOOLEAN
, GUID VARCHAR(36)
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
)
;

-- Step                : write to [tblTraitTypeToCategory]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblTraitTypeToCategory;
CREATE TABLE tblTraitTypeToCategory(
intTTTCID INT
, intTraitCategoryID INT
, intTraitTypeID INT
, vchrDefault VARCHAR(255)
, vchrOverrideValidation VARCHAR(255)
, bitManditory BOOLEAN
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblBiotaDistributionStatic]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblBiotaDistributionStatic;
CREATE TABLE tblBiotaDistributionStatic(
intBiotaID INT
, vchrBiotaParentage VARCHAR(255)
, intDistributionRegionID INT
, vchrDistributionRegionParentage VARCHAR(255)
)
;

-- Step                : write to [tblMaterialIdentification]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMaterialIdentification;
CREATE TABLE tblMaterialIdentification(
intMaterialIdentID INT
, intMaterialID INT
, vchrTaxa VARCHAR(255)
, vchrIDBy VARCHAR(50)
, dtIDDate DATETIME
, intIDRefID INT
, vchrIDMethod VARCHAR(255)
, vchrIDAccuracy VARCHAR(50)
, vchrNameQual VARCHAR(255)
, txtIDNotes MEDIUMTEXT
, vchrIDRefPage VARCHAR(100)
, GUID VARCHAR(36)
, intBasedOnID INT
)
;

-- Step                : write to [tblCharacterDependancy]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblCharacterDependancy;
CREATE TABLE tblCharacterDependancy(
intCharacterDependancyID INT
, intCharacterID INT
, intCharacterStateID INT
, txtNotes MEDIUMTEXT
)
;

-- Step                : write to [tblMorphologyProjectMiscItem]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMorphologyProjectMiscItem;
CREATE TABLE tblMorphologyProjectMiscItem(
intMorphologyProjectMiscItemID INT
, intMorphologyProjectID INT
, vchrCategory VARCHAR(255)
, vchrValue TEXT
, intOrder INT
)
;

-- Step                : write to [tblAuditDelete]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblAuditDelete;
CREATE TABLE tblAuditDelete(
intAuditDeleteID INT
, vchrSourceTable VARCHAR(100)
, vchrDeleteWho VARCHAR(50)
, dtDeleteDatetime DATETIME
, intSourceID INT
, vchrWhoLastUpdated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrDescription TEXT
)
;

-- Step                : write to [tblKeywordType]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblKeywordType;
CREATE TABLE tblKeywordType(
intKeywordTypeID INT
, vchrKeywordType VARCHAR(255)
, intCategoryID INT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblDarwinCoreV1]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblDarwinCoreV1;
CREATE TABLE tblDarwinCoreV1(
intRecordID INT
, intBiotaID INT
, intMaterialID INT
, vchrScientificName VARCHAR(255)
, vchrKingdom VARCHAR(50)
, vchrPhylum VARCHAR(255)
, vchrClass VARCHAR(255)
, vchrOrder VARCHAR(255)
, vchrFamily VARCHAR(255)
, vchrGenus VARCHAR(255)
, vchrSpecies VARCHAR(255)
, vchrSubspecies VARCHAR(255)
, vchrInstitutionCode VARCHAR(50)
, vchrCollectionCode VARCHAR(50)
, vchrCatalogNumber VARCHAR(50)
, vchrCollector VARCHAR(255)
, intYear INT
, intMonth INT
, intDay INT
, vchrCountry VARCHAR(255)
, vchrState VARCHAR(255)
, vchrCounty VARCHAR(255)
, vchrLocality TEXT
, dblLongitude BIGINT
, dblLatitude BIGINT
)
;

-- Step                : write to [tblDichotomousKeyItem]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblDichotomousKeyItem;
CREATE TABLE tblDichotomousKeyItem(
intDKItemID INT
, intMorphologyProjectID INT
, intParentDKItemID INT
, chrType CHAR(1)
, intBiotaID INT
, vchrDescription TEXT
, intImageID INT
, intOrder INT
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, GUID VARCHAR(36)
, vchrHistoryDesc TEXT
)
;

-- Step                : write to [tblTrait]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblTrait;
CREATE TABLE tblTrait(
intTraitID INT
, intTraitCatID INT
, intIntraCatID INT
, intTraitTypeID INT
, vchrValue VARCHAR(255)
, vchrComment TEXT
, bitUseInReports BOOLEAN
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblMorphologyProject]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMorphologyProject;
CREATE TABLE tblMorphologyProject(
intMorphologyProjectID INT
, vchrName VARCHAR(255)
, txtDescription MEDIUMTEXT
, intMorphologyProjectGroupID INT
, bitCreatedByImport BOOLEAN
, sintImportSourceType INT
, vchrDatetimeImported VARCHAR(50)
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, GUID VARCHAR(36)
, chrProjectType CHAR(1)
)
;

-- Step                : write to [tblMultimediaType]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMultimediaType;
CREATE TABLE tblMultimediaType(
intMultimediaTypeID INT
, vchrMultimediaType VARCHAR(255)
, intCategoryID INT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblPhraseCategory]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblPhraseCategory;
CREATE TABLE tblPhraseCategory(
intPhraseCatID INT
, vchrCategory VARCHAR(100)
, bitFixed BOOLEAN
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblCharacter]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblCharacter;
CREATE TABLE tblCharacter(
intCharacterID INT
, vchrName TEXT
, vchrType VARCHAR(10)
, vchrGridText VARCHAR(50)
, vchrDescription TEXT
, tintReliability INT
, intImportedWithProjectID INT
, GUID VARCHAR(36)
, intCharacterGroupID INT
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
)
;

-- Step                : write to [tblGAN]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblGAN;
CREATE TABLE tblGAN(
intBiotaID INT
, refRefCode VARCHAR(50)
, vchrRefPage VARCHAR(50)
, txtRefQual MEDIUMTEXT
, sintDesignation INT
, bitTSCUChgComb BOOLEAN
, vchrTSFixationMethod VARCHAR(255)
, intRefID INT
, GUID VARCHAR(36)
, vchrTypeSpecies TEXT
)
;

-- Step                : write to [tblLabelSetItem]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblLabelSetItem;
CREATE TABLE tblLabelSetItem(
intLabelItemID INT
, intLabelSetID INT
, intItemID INT
, vchrItemType VARCHAR(15)
, intPrintOrder INT
, GUID VARCHAR(36)
, intNumCopies INT
)
;

-- Step                : write to [tblMorphologyView]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMorphologyView;
CREATE TABLE tblMorphologyView(
intMorphologyViewID INT
, intMorphologyViewGroupID INT
, vchrName VARCHAR(255)
, vchrType VARCHAR(10)
, intImportedWithProjectID INT
, GUID VARCHAR(36)
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, bitDefaultView BOOLEAN
)
;

-- Step                : write to [tblMorphologyEntity]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMorphologyEntity;
CREATE TABLE tblMorphologyEntity(
intMorphologyEntityID INT
, tintEntityType INT
, intEntityID INT
, intImportedWithProjectID INT
)
;

-- Step                : write to [tblTraitDefault]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblTraitDefault;
CREATE TABLE tblTraitDefault(
intTraitDefaultID INT
, intTraitTypeID INT
, intMorphologyProjectID INT
, intMorphologyViewID INT
, intCharacterID INT
, intCharacterStateID INT
, intBiotaID INT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblRefLinkType]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblRefLinkType;
CREATE TABLE tblRefLinkType(
intRefLinkTypeID INT
, vchrRefLinkType VARCHAR(255)
, intCategoryID INT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblMorphologyProjectGroup]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMorphologyProjectGroup;
CREATE TABLE tblMorphologyProjectGroup(
intMorphologyProjectGroupID INT
, intParentGroupID INT
, vchrName VARCHAR(255)
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblSAN]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblSAN;
CREATE TABLE tblSAN(
intBiotaID INT
, refRefCode VARCHAR(50)
, vchrRefPage VARCHAR(50)
, txtRefQual MEDIUMTEXT
, vchrPrimaryType VARCHAR(50)
, vchrSecondaryType VARCHAR(50)
, bitPrimaryTypeProbable BOOLEAN
, bitSecondaryTypeProbable BOOLEAN
, intRefID INT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblSANTypeDataType]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblSANTypeDataType;
CREATE TABLE tblSANTypeDataType(
chrKingdomCode VARCHAR(5)
, vchrPrimaryType VARCHAR(50)
, vchrSecondaryTypes VARCHAR(250)
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblLoanReminder]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblLoanReminder;
CREATE TABLE tblLoanReminder(
intLoanReminderID INT
, intLoanID INT
, dtDate DATETIME
, bitClosed BOOLEAN
, txtDescription MEDIUMTEXT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblMaterial]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMaterial;
CREATE TABLE tblMaterial(
intMaterialID INT
, tintTemplate INT
, vchrMaterialName VARCHAR(255)
, intSiteVisitID INT
, vchrAccessionNo VARCHAR(50)
, vchrRegNo VARCHAR(50)
, vchrCollectorNo VARCHAR(50)
, intBiotaID INT
, vchrIDBy VARCHAR(50)
, dtIDDate DATETIME
, intIDRefID INT
, vchrIDMethod VARCHAR(255)
, vchrIDAccuracy VARCHAR(50)
, vchrIDNameQual VARCHAR(255)
, vchrIDNotes VARCHAR(255)
, vchrInstitution VARCHAR(100)
, vchrCollectionMethod VARCHAR(50)
, vchrAbundance VARCHAR(255)
, vchrMacroHabitat VARCHAR(255)
, vchrMicroHabitat VARCHAR(255)
, vchrSource VARCHAR(50)
, intAssociateOf INT
, vchrSpecialLabel TEXT
, vchrOriginalLabel TEXT
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, intTrapID INT
, vchrIDRefPage VARCHAR(100)
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblMaterialPart]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMaterialPart;
CREATE TABLE tblMaterialPart(
intMaterialPartID INT
, intMaterialID INT
, vchrPartName VARCHAR(255)
, vchrSampleType VARCHAR(50)
, intNoSpecimens INT
, vchrNoSpecimensQual VARCHAR(50)
, vchrLifestage VARCHAR(50)
, vchrGender VARCHAR(50)
, vchrRegNo VARCHAR(50)
, vchrCondition VARCHAR(100)
, vchrStorageSite VARCHAR(100)
, vchrStorageMethod VARCHAR(100)
, vchrCurationStatus VARCHAR(100)
, vchrNoOfUnits VARCHAR(100)
, txtNotes MEDIUMTEXT
, tintOnLoan INT
, GUID VARCHAR(36)
, intBasedOnID INT
)
;

-- Step                : write to [tblPoliticalRegion]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblPoliticalRegion;
CREATE TABLE tblPoliticalRegion(
intPoliticalRegionID INT
, vchrName VARCHAR(50)
, intParentID INT
, intOrder INT
, vchrParentage VARCHAR(255)
, vchrRank VARCHAR(255)
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, GUID VARCHAR(36)
, vchrFullName TEXT
, vchrParentCountry VARCHAR(50)
, vchrParentPrimDiv VARCHAR(50)
, vchrparentSecDiv VARCHAR(50)
)
;

-- Step                : write to [tblAutoNumber]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblAutoNumber;
CREATE TABLE tblAutoNumber(
intAutoNumberCatID INT
, vchrCategory VARCHAR(50)
, vchrName VARCHAR(255)
, vchrPrefix VARCHAR(50)
, vchrPostfix VARCHAR(50)
, intNumLeadingZeros INT
, intLastNumber INT
, bitLocked BOOLEAN
, bitEnsureUnique BOOLEAN
)
;

-- Step                : write to [tblDistributionRegion]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblDistributionRegion;
CREATE TABLE tblDistributionRegion(
intDistributionRegionID INT
, vchrName VARCHAR(50)
, intParentID INT
, vchrParentage VARCHAR(255)
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblMorphologyCellState]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMorphologyCellState;
CREATE TABLE tblMorphologyCellState(
intMorphologyCellID INT
, intCharacterStateID INT
)
;

-- Step                : write to [tblRefLink]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblRefLink;
CREATE TABLE tblRefLink(
intRefLinkID INT
, intRefLinkTypeID INT
, intCatID INT
, intIntraCatID INT
, intRefID INT
, vchrRefPage VARCHAR(50)
, txtRefQual MEDIUMTEXT
, intOrder INT
, bitUseInReport BOOLEAN
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblPhrase]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblPhrase;
CREATE TABLE tblPhrase(
intPhraseID INT
, intPhraseCatID INT
, vchrPhrase VARCHAR(255)
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblBiotaDefRank]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblBiotaDefRank;
CREATE TABLE tblBiotaDefRank(
chrCode VARCHAR(5)
, vchrLongName VARCHAR(100)
, vchrTextBeforeInFullName VARCHAR(10)
, vchrTextAfterInFullName VARCHAR(10)
, bitJoinToParentInFullName BOOLEAN
, vchrChecklistDisplayAs VARCHAR(20)
, bitAvailableNameAllowed INT
, bitUnplacedAllowed BOOLEAN
, bitChgCombAllowed BOOLEAN
, bitLituratueNameAllowed BOOLEAN
, chrCategory CHAR(1)
, intOrder INT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblGroupPermission]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblGroupPermission;
CREATE TABLE tblGroupPermission(
intGroupID INT
, intPermID INT
, intPermMask1 INT
, intPermMask2 INT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblFavorite]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblFavorite;
CREATE TABLE tblFavorite(
intFavoriteID INT
, vchrUsername VARCHAR(50)
, intSource INT
, intParentID INT
, tintGroup INT
, vchrGroupName VARCHAR(255)
, intID1 INT
, vchrID2 VARCHAR(255)
, vchrParentage VARCHAR(255)
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblLoanCorrespondence]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblLoanCorrespondence;
CREATE TABLE tblLoanCorrespondence(
intLoanCorrespondenceID INT
, intLoanID INT
, vchrType VARCHAR(50)
, dtDate DATETIME
, intSenderID INT
, intRecipientID INT
, txtDescription MEDIUMTEXT
, vchrRefNo VARCHAR(50)
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblTraitCategory]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblTraitCategory;
CREATE TABLE tblTraitCategory(
intTraitCategoryID INT
, vchrCategory VARCHAR(50)
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblContact]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblContact;
CREATE TABLE tblContact(
intContactID INT
, vchrName VARCHAR(255)
, vchrTitle VARCHAR(50)
, vchrGivenName VARCHAR(255)
, vchrPostalAddress TEXT
, vchrStreetAddress TEXT
, vchrInstitution VARCHAR(255)
, vchrJobTitle VARCHAR(255)
, vchrWorkPh VARCHAR(50)
, vchrWorkFax VARCHAR(50)
, vchrHomePh VARCHAR(50)
, vchrEMail VARCHAR(255)
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblBiotaStorage]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblBiotaStorage;
CREATE TABLE tblBiotaStorage(
intBiotaStorageID INT
, vchrName VARCHAR(50)
, intParentID INT
, vchrParentage VARCHAR(255)
, txtFullPath MEDIUMTEXT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblKeyword]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblKeyword;
CREATE TABLE tblKeyword(
intKeywordID INT
, intKeywordTypeID INT
, intCatID INT
, intIntraCatID INT
, vchrValue VARCHAR(255)
, txtValueQual MEDIUMTEXT
, bitUseInReport BOOLEAN
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblLoan]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblLoan;
CREATE TABLE tblLoan(
intLoanID INT
, vchrLoanNumber VARCHAR(50)
, intRequestorID INT
, intReceiverID INT
, intOriginatorID INT
, dtDateInitiated DATETIME
, dtDateDue DATETIME
, vchrMethodOfTransfer VARCHAR(255)
, vchrPermitNumber VARCHAR(50)
, vchrTypeOfReturn VARCHAR(50)
, vchrRestrictions VARCHAR(255)
, dtDateClosed DATETIME
, bitLoanClosed BOOLEAN
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblBiotaDistribution]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblBiotaDistribution;
CREATE TABLE tblBiotaDistribution(
intBiotaDistID INT
, intBiotaID INT
, intDistributionRegionID INT
, bitIntroduced BOOLEAN
, bitUncertain BOOLEAN
, txtQual MEDIUMTEXT
, GUID VARCHAR(36)
, bitThroughoutRegion BOOLEAN
)
;

-- Step                : write to [tblMorphologyViewAlias]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMorphologyViewAlias;
CREATE TABLE tblMorphologyViewAlias(
intMorphologyViewAliasID INT
, intMorphologyViewID INT
, intSourceID INT
, chrSourceType CHAR(1)
, vchrAlias TEXT
, GUID VARCHAR(36)
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
)
;

-- Step                : write to [ttblNotes]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS ttblNotes;
CREATE TABLE ttblNotes(
biotaID INT
, overview MEDIUMTEXT
, description MEDIUMTEXT
)
;

-- Step                : write to [tblBiotaDefRules]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblBiotaDefRules;
CREATE TABLE tblBiotaDefRules(
chrKingdomCode VARCHAR(2)
, chrRankCode VARCHAR(20)
, vchrValidChildList VARCHAR(255)
, vchrValidEndingList VARCHAR(255)
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblNote]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblNote;
CREATE TABLE tblNote(
intNoteID INT
, intNoteTypeID INT
, intCatID INT
, intIntraCatID INT
, txtNote MEDIUMTEXT
, bitUseInReports BOOLEAN
, GUID VARCHAR(36)
, vchrAuthor VARCHAR(255)
, intRefID INT
, vchrRefPages VARCHAR(100)
, txtComments MEDIUMTEXT
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
)
;

-- Step                : write to [tblGroup]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblGroup;
CREATE TABLE tblGroup(
intGroupID INT
, vchrName VARCHAR(30)
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblMultimediaLink]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMultimediaLink;
CREATE TABLE tblMultimediaLink(
intMultimediaLinkID INT
, intMultimediaTypeID INT
, intCatID INT
, intIntraCatID INT
, intMultimediaID INT
, vchrCaption TEXT
, bitUseInReport BOOLEAN
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblBiotaLocation]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblBiotaLocation;
CREATE TABLE tblBiotaLocation(
intBiotaLocationID INT
, intBiotaID INT
, intBiotaStorageID INT
, txtNotes MEDIUMTEXT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblMorphologyProjectCharGroup]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMorphologyProjectCharGroup;
CREATE TABLE tblMorphologyProjectCharGroup(
intMorphologyProjectCharGroupID INT
, intMorphologyProjectID INT
, intParentID INT
, ItemType VARCHAR(2)
, vchrName VARCHAR(255)
, intCharacterID INT
, intOrder INT
, vchrParentage TEXT
, GUID VARCHAR(36)
, vchrWhoCreated VARCHAR(50)
, dtDateCreated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, dtDateLastUpdated DATETIME
)
;

-- Step                : write to [tblReference]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblReference;
CREATE TABLE tblReference(
intRefID INT
, vchrRefCode VARCHAR(50)
, vchrAuthor TEXT
, vchrTitle TEXT
, vchrBookTitle TEXT
, vchrEditor TEXT
, vchrRefType VARCHAR(20)
, vchrYearOfPub VARCHAR(10)
, vchrActualDate VARCHAR(50)
, intJournalID INT
, vchrPartNo VARCHAR(50)
, vchrSeries VARCHAR(50)
, vchrPublisher VARCHAR(200)
, vchrPlace VARCHAR(200)
, vchrVolume VARCHAR(50)
, vchrPages VARCHAR(50)
, vchrTotalPages VARCHAR(50)
, vchrPossess VARCHAR(50)
, vchrSource VARCHAR(50)
, vchrEdition VARCHAR(50)
, vchrISBN VARCHAR(30)
, vchrISSN VARCHAR(30)
, txtQual MEDIUMTEXT
, txtDateQual MEDIUMTEXT
, txtFullText MEDIUMTEXT
, txtFullRTF MEDIUMTEXT
, intStartPage INT
, intEndPage INT
, txtAbstract MEDIUMTEXT
, GUID VARCHAR(36)
, vchrWhoCreated VARCHAR(50)
, dtDateCreated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, dtDateLastUpdated DATETIME
)
;

-- Step                : write to [tblMorphologyProjectEntity]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMorphologyProjectEntity;
CREATE TABLE tblMorphologyProjectEntity(
intMorphologyProjectEntityID INT
, intMorphologyProjectID INT
, intMorphologyEntityID INT
, intOrder INT
, bitHidden BOOLEAN
, GUID VARCHAR(36)
, vchrWhoInserted VARCHAR(50)
, dtDatetimeInserted DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, dtDateTimeLastUpdated DATETIME
)
;

-- Step                : write to [tblBiotaDefKingdom]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblBiotaDefKingdom;
CREATE TABLE tblBiotaDefKingdom(
chrKingdomCode VARCHAR(2)
, vchrKingdomName VARCHAR(50)
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblCurationEvent]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblCurationEvent;
CREATE TABLE tblCurationEvent(
intCurationEventID INT
, intMaterialID INT
, vchrSubpartName VARCHAR(255)
, vchrWho VARCHAR(50)
, dtWhen DATETIME
, vchrEventType VARCHAR(100)
, txtEventDesc MEDIUMTEXT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblLoanMaterial]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblLoanMaterial;
CREATE TABLE tblLoanMaterial(
intLoanMaterialID INT
, intLoanID INT
, intMaterialID INT
, vchrNumSpecimens VARCHAR(50)
, vchrTaxonName VARCHAR(100)
, vchrMaterialDescription TEXT
, dtDateAdded DATETIME
, dtDateReturned DATETIME
, bitReturned BOOLEAN
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblTraitType]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblTraitType;
CREATE TABLE tblTraitType(
intTraitTypeID INT
, vchrTraitType VARCHAR(255)
, vchrDataType VARCHAR(20)
, vchrValidationStr VARCHAR(255)
, intCategoryID INT
, GUID VARCHAR(36)
)
;

-- Step                : write to [tblMultimedia]
-- Database Connection : dataLoadServer
-- SQL
DROP TABLE IF EXISTS tblMultimedia;
CREATE TABLE tblMultimedia(
intMultimediaID INT
, vchrName VARCHAR(255)
, vchrFileExtension VARCHAR(20)
, intSizeInBytes INT
, imgMultimedia LONGBLOB
, vchrNumber VARCHAR(255)
, vchrArtist VARCHAR(255)
, vchrDateRecorded VARCHAR(50)
, vchrOwner VARCHAR(255)
, txtCopyright MEDIUMTEXT
, dtDateCreated DATETIME
, vchrWhoCreated VARCHAR(50)
, dtDateLastUpdated DATETIME
, vchrWhoLastUpdated VARCHAR(50)
, GUID VARCHAR(36)
)
;

-- Add indexes to support transformations.

ALTER TABLE tblSite
	ADD PRIMARY KEY (intSiteID),
	ADD INDEX (intPoliticalRegionID);
ALTER TABLE tblSiteVisit
	ADD PRIMARY KEY (intSiteVisitID),
	ADD INDEX (intSiteID);
ALTER TABLE tblTrait
	ADD PRIMARY KEY (intTraitID),
	ADD INDEX (intTraitCatID),
	ADD INDEX (intIntraCatID),
	ADD INDEX (intTraitTypeID);
ALTER TABLE tblTraitCategory
	ADD PRIMARY KEY (intTraitCategoryID),
	ADD INDEX (vchrCategory);
ALTER TABLE tblTraitType
	ADD PRIMARY KEY (intTraitTypeID),
# 	Current max length of vchrTraitType is 27.
	ADD INDEX (vchrTraitType(30));
ALTER TABLE tblTraitType
	ADD INDEX (intCategoryID);
ALTER TABLE tblPoliticalRegion
	ADD PRIMARY KEY (intPoliticalRegionID),
	ADD INDEX (intParentID),
	ADD INDEX (vchrName);
ALTER TABLE tblMaterial
	ADD PRIMARY KEY (intMaterialID),
	ADD INDEX (intSiteVisitID),
	ADD INDEX (vchrAccessionNo),
	ADD INDEX (intBiotaID),
	ADD INDEX (vchrSource);
ALTER TABLE tblMaterial
	ADD INDEX (vchrIDMethod),
	ADD INDEX (vchrIDNameQual),
	ADD INDEX (dtIDDate),
	ADD INDEX (vchrIDBy),
	ADD INDEX (vchrIDRefPage),
	ADD INDEX (intIDRefID);
ALTER TABLE tblMaterialPart
	ADD INDEX (intMaterialID),
 	ADD INDEX (intMaterialPartID),
 	ADD INDEX (vchrSampleType),
 	ADD INDEX (vchrGender),
 	ADD INDEX (vchrRegNo),
 	ADD INDEX (vchrLifestage),
 	ADD INDEX (vchrStorageMethod),
 	ADD INDEX (vchrPartName)
;


ALTER TABLE tblBiota
	ADD PRIMARY KEY (intBiotaID),
	ADD INDEX (intParentID),
	ADD INDEX (vchrEpithet(58)),
	ADD INDEX (vchrAuthor(32)),
	ADD INDEX (chrElemType(5)),
	ADD INDEX (vchrRank(12)),
	ADD INDEX (vchrParentage (85)),
	ADD INDEX (vchrFullName (75)),
	ADD INDEX (dtDateCreated),
	ADD INDEX (vchrWhoCreated(22)),
	ADD INDEX (dtDateLastUpdated),
	ADD INDEX (vchrWhoLastUpdated(12)),
	ADD INDEX (intImportedWithProjectID(4)),
	ADD INDEX (vchrAvailableNameStatus (33)),
	ADD INDEX (vchrParentSubspecies(19)),
	ADD INDEX (vchrParentFamily(30)),
	ADD INDEX (vchrParentOrder(30)),
	ADD INDEX (vchrParentSpecies(40)),
	ADD INDEX (vchrParentGenus(30)),
	ADD INDEX (vchrParentPhylum(30)),
	ADD INDEX (vchrParentKingdom(30))
;
ALTER TABLE tblBiotaDefRank
	ADD PRIMARY KEY (chrCode),
	ADD INDEX (chrCode),
	ADD INDEX (vchrLongName),
	ADD INDEX (vchrTextBeforeInFullName),
	ADD INDEX (vchrTextAfterInFullName),
	ADD INDEX (bitJoinToParentInFullName),
	ADD INDEX (vchrChecklistDisplayAs),
	ADD INDEX (bitAvailableNameAllowed),
	ADD INDEX (bitUnplacedAllowed),
	ADD INDEX (bitChgCombAllowed),
	ADD INDEX (bitLituratueNameAllowed),
	ADD INDEX (chrCategory),
	ADD INDEX (intOrder),
	ADD INDEX (GUID);
ALTER TABLE tblNote ADD PRIMARY KEY (intNoteID),
	ADD INDEX (intNoteTypeID),
	ADD INDEX (intCatID),
	ADD INDEX (intIntraCatID),
	ADD INDEX (intRefID),
	ADD INDEX (GUID);
ALTER TABLE tblNote
	ADD INDEX (dtDateCreated),
	ADD INDEX (bitUseInReports)
	;
ALTER TABLE tblNoteType ADD PRIMARY KEY (intNoteTypeID),
ADD INDEX (vchrNoteType(20)),
ADD INDEX (intCategoryID);
ALTER TABLE tblReference ADD PRIMARY KEY (intRefID);
ALTER TABLE tblReference ADD INDEX (vchrRefCode);
ALTER TABLE tblCommonName ADD PRIMARY KEY (intCommonNameID),
	ADD INDEX (intBiotaID),
	ADD INDEX (intRefID),
	ADD INDEX (vchrCommonName),
	ADD INDEX (vchrRefCode);

ALTER TABLE tblAssociate ADD PRIMARY KEY (intAssociateID),
	ADD INDEX (intToIntraCatID),
	ADD INDEX (intFromCatID);
