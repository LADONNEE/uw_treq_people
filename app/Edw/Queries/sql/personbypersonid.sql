SELECT TOP 1 
  p.PersonKey,
  p.DisplayName,
  p.DisplayName,
  p.DisplayFirstName,
  p.DisplayLastName,
  p.EmployeeID,
  p.RegID,
  p.UWNetID,
  p.StudentId

FROM sec.Person p

WHERE p.PersonKey LIKE __MATCH__ ;



