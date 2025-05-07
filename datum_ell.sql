--------------------------------------------------------
--  File created - szerda-május-07-2025   
--------------------------------------------------------
--------------------------------------------------------
--  DDL for Trigger DATUM_ELL
--------------------------------------------------------

  CREATE OR REPLACE NONEDITIONABLE TRIGGER "SYSTEM"."DATUM_ELL" 
BEFORE INSERT OR UPDATE ON events
FOR EACH ROW
BEGIN
  IF :NEW.end_time < :NEW.start_time THEN
    RAISE_APPLICATION_ERROR(-20001, 'A végdátum nem lehet korábban, mint a kezdődátum.');
  END IF;
END;
/
ALTER TRIGGER "SYSTEM"."DATUM_ELL" ENABLE;
