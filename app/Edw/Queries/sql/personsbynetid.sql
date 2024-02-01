SELECT TOP 10 
  p.PersonKey,
  p.DisplayName,
  p.DisplayName,
  COALESCE(p.DisplayFirstName, p.DisplayFirstName) AS LegalFirstName,
  COALESCE(p.DisplayLastName, p.DisplayLastName) AS LegalLastName,
  p.EmployeeID,
  p.RegID,
  p.UWNetID,
  p.StudentId

FROM sec.Person p

WHERE p.UWNetID LIKE __MATCH__ 
ORDER BY LEN(p.UWNetID) ASC;



