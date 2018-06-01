drop table if exists collectionTwoItemPartSplit;
create table collectionTwoItemPartSplit as select
  @newMaterialId:=(IF(
    masterSecondary.isMaster = 0,
    concat_ws('_', it.intMaterialID, it.intMaterialPartID),
    it.intMaterialID
  )) as newMaterialId,
  (@row_number:=CASE
  WHEN
    @new_material_id = @newMaterialId
  THEN
    @row_number + 1
  ELSE
    1
  END) AS accessionSuffix,
  @new_material_id:=@newMaterialId,
  m.vchrAccessionNo,
  it.intMaterialID,
  masterSecondary.accOrder,
  masterSecondary.itemType,
  pm.preparationMethod as preparationMethod,
  it.intMaterialPartID as MaterialPartID,
  it.vchrPartName as PartPartName,
  it.vchrSampleType as PartSampleType,
  it.intNoSpecimens as PartNoSpecimens,
  it.vchrNoSpecimensQual as PartNoSpecimensQual,
  it.vchrLifestage as PartLifestage,
  it.vchrGender as PartGender,
  it.vchrRegNo as PartRegNo,
  it.vchrCondition as PartCondition,
  it.vchrStorageSite as PartStorageSite,
  it.vchrStorageMethod as PartStorageMethod,
  it.vchrCurationStatus as PartCurationStatus,
  it.vchrNoOfUnits as PartNoOfUnits,
  it.txtNotes as PartNotes,
  it.tintOnLoan as PartOnLoan,
  it.GUID as PartGUID,
  it.intBasedOnID as PartBasedOnID,
  masterSecondary.isMaster,
  if (it.vchrStorageMethod like '%wing%', 'wing', null) as preparationType
from
  splitItemType it
inner join
(
select
  it2.intMaterialPartId,
  it2.accOrder,
  itmaster.itemType,
  1 as isMaster
from
  splitItemType it2
  inner join
  (
    select
      min(itmaster.accOrder) as minAccOrder,
      itmaster.intMaterialPartId,
      itemType
    from
      splitItemType itmaster
    group by
      itmaster.intMaterialPartId
  ) itmaster
    on
      it2.intMaterialPartId = itmaster.intMaterialPartId
      and it2.accOrder = itmaster.minAccOrder
UNION ALL
select
  itSecondary.intMaterialPartId,
  itSecondary.accOrder,
  itSecondary.itemType,
  0 as isMaster
from
  splitItemType itSecondary
inner join
(
  select
    max(itmaster2.accOrder) as maxAccOrder,
    itmaster2.intMaterialPartId,
    itemType,
    accOrder
  from
    splitItemType itmaster2
  group by
    itmaster2.intMaterialPartId
) itmaster2
  on
    itSecondary.intMaterialPartId = itmaster2.intMaterialPartId
    and itSecondary.accOrder <> itmaster2.accOrder
where
  itmaster2.intMaterialPartId IS NOT NULL
) masterSecondary
on
  it.intMaterialPartId = masterSecondary.intMaterialPartId
  and it.accOrder = masterSecondary.accOrder
left join
  splitPreparationMethod pm
on
  it.intMaterialPartId = pm.intMaterialPartId
join
  tblMaterial m
on
  it.intMaterialId = m.intMaterialId
order by
  it.intMaterialID,
  accOrder desc;

alter table collectionTwoItemPartSplit
  add index (MaterialPartID),
  add index (itemType),
  add index (preparationMethod),
  add index (newMaterialId),
  add index (intMaterialID)
;
