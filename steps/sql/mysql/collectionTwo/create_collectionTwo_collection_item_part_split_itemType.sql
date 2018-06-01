drop table if exists splitItemType;
create table splitItemType as select
    accOrder,
    intMaterialID,
    itemType,
    vchrSampleType,
    intMaterialPartID,
    vchrPartName,
    intNoSpecimens,
    vchrNoSpecimensQual,
    vchrLifestage,
    vchrGender,
    vchrRegNo,
    vchrCondition,
    vchrStorageSite,
    vchrStorageMethod,
    vchrCurationStatus,
    vchrNoOfUnits,
    txtNotes,
    tintOnLoan,
    GUID,
    intBasedOnID
  from (
    select 1 as accOrder, intMaterialPartID, 'pin' as itemType, intMaterialID, vchrPartName, vchrSampleType, intNoSpecimens, vchrNoSpecimensQual, vchrLifestage, vchrGender, vchrRegNo, vchrCondition, vchrStorageSite, vchrStorageMethod, vchrCurationStatus, vchrNoOfUnits, txtNotes, tintOnLoan, GUID, intBasedOnID from tblMaterialPart
    where
      (
        (
          vchrStorageMethod like '%pin%'
          or vchrStorageMethod like '%point%'
        )
        and
        not vchrStorageMethod like '%capsule%'
      )
      or
      vchrStorageMethod like '%card%'
    UNION ALL
    select 1 as accOrder, intMaterialPartID, 'pin and capsule' as itemType, intMaterialID, vchrPartName, vchrSampleType, intNoSpecimens, vchrNoSpecimensQual, vchrLifestage, vchrGender, vchrRegNo, vchrCondition, vchrStorageSite, vchrStorageMethod, vchrCurationStatus, vchrNoOfUnits, txtNotes, tintOnLoan, GUID, intBasedOnID from tblMaterialPart
    where
      (
        vchrStorageMethod like '%pin%'
        or vchrStorageMethod like '%pointed%'
      )
      and
      (
        vchrStorageMethod like '%capsule%'
        or vchrStorageMethod like '%capped%'
      )
    UNION ALL
    select 2 as accOrder, intMaterialPartID, 'vial' as itemType, intMaterialID, vchrPartName, vchrSampleType, intNoSpecimens, vchrNoSpecimensQual, vchrLifestage, vchrGender, vchrRegNo, vchrCondition, vchrStorageSite, vchrStorageMethod, vchrCurationStatus, vchrNoOfUnits, txtNotes, tintOnLoan, GUID, intBasedOnID from tblMaterialPart
    where
      vchrStorageMethod like '%alcohol%'
      or vchrStorageMethod like '%ethanol%'
      or vchrStorageMethod like '%spirit%'
    UNION ALL
    -- TODO: what order is SEM?
      select 99 as accOrder, intMaterialPartID, 'SEM' as itemType, intMaterialID, vchrPartName, vchrSampleType, intNoSpecimens, vchrNoSpecimensQual, vchrLifestage, vchrGender, vchrRegNo, vchrCondition, vchrStorageSite, vchrStorageMethod, vchrCurationStatus, vchrNoOfUnits, txtNotes, tintOnLoan, GUID, intBasedOnID from tblMaterialPart
      where
        vchrStorageMethod like '%SEM%'
    UNION ALL
      select 3 as accOrder, intMaterialPartID, 'slide' as itemType, intMaterialID, vchrPartName, vchrSampleType, intNoSpecimens, vchrNoSpecimensQual, vchrLifestage, vchrGender, vchrRegNo, vchrCondition, vchrStorageSite, vchrStorageMethod, vchrCurationStatus, vchrNoOfUnits, txtNotes, tintOnLoan, GUID, intBasedOnID from tblMaterialPart
      where
        vchrStorageMethod like '%slide%'
    UNION ALL
      select 4 as accOrder, intMaterialPartID, 'envelope' as itemType, intMaterialID, vchrPartName, vchrSampleType, intNoSpecimens, vchrNoSpecimensQual, vchrLifestage, vchrGender, vchrRegNo, vchrCondition, vchrStorageSite, vchrStorageMethod, vchrCurationStatus, vchrNoOfUnits, txtNotes, tintOnLoan, GUID, intBasedOnID from tblMaterialPart
      where
        vchrStorageMethod like '%envelope%'
    UNION ALL
      select 5 as accOrder, intMaterialPartID, 'paper triangle' as itemType, intMaterialID, vchrPartName, vchrSampleType, intNoSpecimens, vchrNoSpecimensQual, vchrLifestage, vchrGender, vchrRegNo, vchrCondition, vchrStorageSite, vchrStorageMethod, vchrCurationStatus, vchrNoOfUnits, txtNotes, tintOnLoan, GUID, intBasedOnID from tblMaterialPart
      where
        vchrStorageMethod like '%paper%triangle%'
    UNION ALL
      select 6 as accOrder, intMaterialPartID, 'timber encased' as itemType, intMaterialID, vchrPartName, vchrSampleType, intNoSpecimens, vchrNoSpecimensQual, vchrLifestage, vchrGender, vchrRegNo, vchrCondition, vchrStorageSite, vchrStorageMethod, vchrCurationStatus, vchrNoOfUnits, txtNotes, tintOnLoan, GUID, intBasedOnID from tblMaterialPart
      where
        vchrStorageMethod like '%timber%encased'
    UNION ALL
      select 7 as accOrder, intMaterialPartID, 'in seed pod' as itemType, intMaterialID, vchrPartName, vchrSampleType, intNoSpecimens, vchrNoSpecimensQual, vchrLifestage, vchrGender, vchrRegNo, vchrCondition, vchrStorageSite, vchrStorageMethod, vchrCurationStatus, vchrNoOfUnits, txtNotes, tintOnLoan, GUID, intBasedOnID from tblMaterialPart
      where
        vchrStorageMethod like '%seed%pod%'
    UNION ALL
      select 8 as accOrder, intMaterialPartID, 'on original leaf' as itemType, intMaterialID, vchrPartName, vchrSampleType, intNoSpecimens, vchrNoSpecimensQual, vchrLifestage, vchrGender, vchrRegNo, vchrCondition, vchrStorageSite, vchrStorageMethod, vchrCurationStatus, vchrNoOfUnits, txtNotes, tintOnLoan, GUID, intBasedOnID from tblMaterialPart
      where
        vchrStorageMethod like '%original%leaf%'
    UNION ALL
      select 9 as accOrder, intMaterialPartID, 'bar coded tube' as itemType, intMaterialID, vchrPartName, vchrSampleType, intNoSpecimens, vchrNoSpecimensQual, vchrLifestage, vchrGender, vchrRegNo, vchrCondition, vchrStorageSite, vchrStorageMethod, vchrCurationStatus, vchrNoOfUnits, txtNotes, tintOnLoan, GUID, intBasedOnID from tblMaterialPart
      where
        vchrStorageMethod like '%bar%coded%tube%'
    UNION ALL
      select 10 as accOrder, intMaterialPartID, 'field record only' as itemType, intMaterialID, vchrPartName, vchrSampleType, intNoSpecimens, vchrNoSpecimensQual, vchrLifestage, vchrGender, vchrRegNo, vchrCondition, vchrStorageSite, vchrStorageMethod, vchrCurationStatus, vchrNoOfUnits, txtNotes, tintOnLoan, GUID, intBasedOnID from tblMaterialPart
      where
        vchrStorageMethod like '%field%record%'
    UNION ALL
      select 11 as accOrder, intMaterialPartID, 'lost/destroyed' as itemType, intMaterialID, vchrPartName, vchrSampleType, intNoSpecimens, vchrNoSpecimensQual, vchrLifestage, vchrGender, vchrRegNo, vchrCondition, vchrStorageSite, vchrStorageMethod, vchrCurationStatus, vchrNoOfUnits, txtNotes, tintOnLoan, GUID, intBasedOnID from tblMaterialPart
      where
        vchrStorageMethod like '%lost%destroyed%'
  ) as ciItemType;

alter table splitItemType
  add index (intMaterialID),
  add index (intMaterialPartID),
  add index (itemType);

