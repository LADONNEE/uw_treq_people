SELECT DISTINCT
  p.PersonKey,
  p.DisplayName,
  p.DisplayFirstName AS LegalFirstName,
  p.DisplayLastName AS LegalLastName,
  p.EmployeeID,
  p.RegID,
  p.UWNetID,
  p.StudentId
  FROM UWODS.sec.Person p
WHERE __WHERESTATEMENT__ ;