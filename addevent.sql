--------------------------------------------------------
--  File created - szerda-május-07-2025   
--------------------------------------------------------
--------------------------------------------------------
--  DDL for Procedure ADDEVENT
--------------------------------------------------------
set define off;

  CREATE OR REPLACE NONEDITIONABLE PROCEDURE "SYSTEM"."ADDEVENT" (    
    user_id       IN NUMBER,
    calendar_id   IN NUMBER,
    title         IN VARCHAR2,
    description   IN CLOB,
    start_time    IN TIMESTAMP,
    end_time      IN TIMESTAMP,
    location      IN VARCHAR2) AS 
BEGIN
  INSERT INTO EVENTS (event_id, user_id, calendar_id, title, description, start_time, end_time, location, is_recurring) VALUES (EVENT_SEQ.NEXTVAL, user_id, calendar_id, title, description, start_time, end_time, location, 'FALSE');
END ADDEVENT;

/
