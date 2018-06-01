drop table if exists splitPreparationMethod;
create table splitPreparationMethod as select
  *
from (
    select intMaterialID, intMaterialPartID, '-80 freezer' as preparationMethod, vchrstoragemethod from tblMaterialPart
    where
      vchrStorageMethod like '%-80%freezer%'
  UNION ALL
    select intMaterialID, intMaterialPartID, '-20 freezer' as preparationMethod, vchrstoragemethod from tblMaterialPart
    where
      vchrStorageMethod like '%-20%freezer%'
  UNION ALL
    select intMaterialID, intMaterialPartID, 'stage-mount' as preparationMethod, vchrstoragemethod from tblMaterialPart
    where
      vchrStorageMethod like '%stage%mount%'
  UNION ALL
    select intMaterialID, intMaterialPartID, 'returned to collector' as preparationMethod, vchrstoragemethod from tblMaterialPart
    where
      vchrStorageMethod like '%returned to collector%'
  UNION ALL
    select intMaterialID, intMaterialPartID, 'carded' as preparationMethod, vchrstoragemethod from tblMaterialPart
    where
      vchrStorageMethod like '%card%' or vchrStorageMethod like '%point%'
  UNION ALL
    select intMaterialID, intMaterialPartID, 'glycerol' as preparationMethod, vchrstoragemethod from tblMaterialPart
    where
      vchrStorageMethod like '%glycerol%'
  UNION ALL
    select intMaterialID, intMaterialPartID, 'ethanol' as preparationMethod, vchrstoragemethod from tblMaterialPart
    where
      regexp_instr(vchrStorageMethod, 'ethanol') and not regexp_instr(vchrStorageMethod, '[0-9]{0, 2}\%.*ethanol')
  UNION ALL
    select intMaterialID, intMaterialPartID, 'gold coated' as preparationMethod, vchrstoragemethod from tblMaterialPart
    where
      vchrStorageMethod like '%gold%coated%'
  UNION ALL
  -- TODO: keep alcohol?
    select intMaterialID, intMaterialPartID, 'alcohol' as preparationMethod, vchrstoragemethod from tblMaterialPart
    where
      vchrStorageMethod like '%alcohol%'
  UNION ALL
    select intMaterialID, intMaterialPartID, '70% ethanol' as preparationMethod, vchrstoragemethod from tblMaterialPart
    where
      vchrStorageMethod like '%70%ethanol%'
  UNION ALL
    select intMaterialID, intMaterialPartID, '80% ethanol' as preparationMethod, vchrstoragemethod from tblMaterialPart
    where
      vchrStorageMethod like '%80%ethanol%'
  UNION ALL
    select intMaterialID, intMaterialPartID, '95% ethanol' as preparationMethod, vchrstoragemethod from tblMaterialPart
    where
      vchrStorageMethod like '%95%ethanol%'
  UNION ALL
    select intMaterialID, intMaterialPartID, '100% ethanol' as preparationMethod, vchrstoragemethod from tblMaterialPart
    where
      regexp_instr(vchrStorageMethod, '(absolute|100\%)[[:space:]]*ethanol')
) as ciPreparationMethod;

alter table splitPreparationMethod
  add index (intMaterialID),
  add index (intMaterialPartID),
  add index (preparationMethod);
