--------------------------------------------------------
--  File created - vasárnap-május-18-2025   
--------------------------------------------------------
--------------------------------------------------------
--  DDL for Procedure EDITEVENT
--------------------------------------------------------
set define off;

  CREATE OR REPLACE NONEDITIONABLE PROCEDURE "SYSTEM"."EDITEVENT" (   
    u_event_id       IN NUMBER,
    u_title         IN VARCHAR2,
    u_description   IN CLOB,
    u_start_time    IN TIMESTAMP,
    u_end_time      IN TIMESTAMP,
    u_location      IN VARCHAR2) AS 
BEGIN
   UPDATE EVENTS SET
   title = u_title, description = u_description, start_time = u_start_time, end_time = u_end_time, location = u_location
   WHERE event_id=u_event_id;
END EDITEVENT;

/
