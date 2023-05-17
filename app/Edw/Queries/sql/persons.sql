SELECT DISTINCT
  p.PersonKey,
  p.LegalName,
  p.DisplayName,
  CASE WHEN CHARINDEX(' ,',REVERSE(p.DisplayName)) = 0 THEN p.LegalFirstName
  ELSE RIGHT(p.DisplayName, CHARINDEX(' ,',REVERSE(p.DisplayName)) -1 )
  END AS LegalFirstName,
  CASE WHEN CHARINDEX(', ',p.DisplayName) = 0 THEN p.LegalLastName
  ELSE LEFT(p.DisplayName, CHARINDEX(', ',p.DisplayName)-1)
  END AS LegalLastName,
  p.EmployeeID,
  p.RegID,
  p.UWNetID,
  p.StudentId
  
  FROM HumanResources.sec.WorkerPosition wp
INNER JOIN (
  SELECT PersonKey, PositionKey, MAX(RecordEffBeginDate) AS RecordEffBeginDate
  FROM HumanResources.sec.WorkerPosition
  GROUP BY PersonKey, PositionKey
) as wp_latest
  ON wp.PersonKey = wp_latest.PersonKey
  AND wp.PositionKey = wp_latest.PositionKey
  AND wp.RecordEffBeginDate = wp_latest.RecordEffBeginDate
INNER JOIN (
  SELECT DISTINCT PersonKey
  FROM HumanResources.sec.WorkerPosition pop_wp
  INNER JOIN HumanResources.sec.Position pop_pos
  ON pop_wp.PositionKey = pop_pos.PositionKey
  WHERE pop_pos.SupervisoryOrgID LIKE __MATCH__
  AND pop_pos.RecordEffEndDate > __VALIDITY__
) AS population
  ON wp.PersonKey = population.PersonKey
INNER JOIN HumanResources.sec.Person p
  ON wp.PersonKey = p.PersonKey
  AND p.RecordUpdateDttm IS NULL
INNER JOIN HumanResources.sec.Position pos
  ON wp.PositionKey = pos.PositionKey
  AND wp.RecordEffEndDate >= pos.RecordEffBeginDate
  AND wp.RecordEffEndDate <= pos.RecordEffEndDate
INNER JOIN HumanResources.sec.JobProfile prof
  ON pos.JobProfileKey = prof.JobProfileKey
  AND wp.RecordEffEndDate >= prof.RecordEffBeginDate
  AND wp.RecordEffEndDate <= prof.RecordEffEndDate
INNER JOIN HumanResources.sec.SupervisoryOrg org
  ON pos.SupervisoryOrgKey = org.SupervisoryOrgKey
  AND wp.RecordEffEndDate >= org.RecordEffBeginDate
  AND wp.RecordEffEndDate <= org.RecordEffEndDate
LEFT JOIN HumanResources.sec.Person MGR
  ON org.ManagerKey = MGR.PersonKey
  AND MGR.RecordUpdateDttm IS NULL
WHERE wp.ECSCode <> 'S' AND p.UWNetID IS NOT NULL