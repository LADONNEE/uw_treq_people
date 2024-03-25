SELECT DISTINCT
  p.PersonKey,
    empmgr.EmployeeID,
  p.DisplayName,
    p.DisplayFirstName,
    p.DisplayLastName,
  p.RegID,
  p.UWNetID,
  p.StudentId
FROM UWODS.sec.Person p
INNER JOIN (
    SELECT emd.PersonKey AS PersonKey, emd.EmployeeID
    FROM UWODS.sec.EmploymentDetail emd
    WHERE emd.SupervisoryOrgID LIKE __MATCH__
        AND ( emd.PositionVacateDate IS NULL   OR  emd.PositionVacateDate > __VALIDITY__)
) empmgr
ON p.PersonKey = empmgr.PersonKey
WHERE p.UWNetID IS NOT NULL
