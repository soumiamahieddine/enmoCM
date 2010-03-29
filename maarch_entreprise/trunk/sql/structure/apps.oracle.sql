--------------------------------------------------------
--  DDL for Table DOCTYPES_FIRST_LEVEL
--------------------------------------------------------

  CREATE TABLE "DOCTYPES_FIRST_LEVEL" 
   (	"DOCTYPES_FIRST_LEVEL_ID" NUMBER NOT NULL ENABLE, 
	"DOCTYPES_FIRST_LEVEL_LABEL" VARCHAR2(255) NOT NULL ENABLE, 
	"ENABLED" VARCHAR2(1) DEFAULT 'Y', 
	 PRIMARY KEY ("DOCTYPES_FIRST_LEVEL_ID") ENABLE
   ) ;
   
--------------------------------------------------------
--  DDL for Table DOCTYPES_SECOND_LEVEL
--------------------------------------------------------

  CREATE TABLE "DOCTYPES_SECOND_LEVEL" 
   (	"DOCTYPES_SECOND_LEVEL_ID" NUMBER NOT NULL ENABLE, 
	"DOCTYPES_SECOND_LEVEL_LABEL" VARCHAR2(255) NOT NULL ENABLE, 
	"DOCTYPES_FIRST_LEVEL_ID" NUMBER NOT NULL ENABLE, 
	"ENABLED" VARCHAR2(1) DEFAULT 'Y', 
	 PRIMARY KEY ("DOCTYPES_SECOND_LEVEL_ID") ENABLE
   ) ;
   
CREATE TABLE CONTACTS
(
  CONTACT_ID           NUMBER                   NOT NULL,
  LASTNAME             VARCHAR2(255 BYTE),
  FIRSTNAME            VARCHAR2(255 BYTE),
  SOCIETY              VARCHAR2(255 BYTE),
  FUNCTION             VARCHAR2(255 BYTE),
  ADDRESS_NUM          VARCHAR2(255 BYTE),
  ADDRESS_STREET       VARCHAR2(255 BYTE),
  ADDRESS_COMPLEMENT   VARCHAR2(255 BYTE),
  ADDRESS_TOWN         VARCHAR2(255 BYTE),
  ADDRESS_POSTAL_CODE  VARCHAR2(255 BYTE),
  ADDRESS_COUNTRY      VARCHAR2(255 BYTE),
  EMAIL                VARCHAR2(255 BYTE),
  PHONE                VARCHAR2(20 BYTE),
  OTHER_DATA           CLOB,
  IS_CORPORATE_PERSON  CHAR(1 BYTE)             DEFAULT 'Y'                   ,
  USER_ID              VARCHAR2(32 BYTE),
  TITLE                VARCHAR2(255 BYTE),
  ENABLED              CHAR(1 BYTE)             DEFAULT 'Y'                   
)
PCTUSED    0
PCTFREE    10
INITRANS   1
MAXTRANS   255
STORAGE    (
            INITIAL          64K
            MINEXTENTS       1
            MAXEXTENTS       2147483645
            PCTINCREASE      0
            BUFFER_POOL      DEFAULT
           )
LOGGING 
NOCOMPRESS 
NOCACHE
NOPARALLEL
MONITORING;
   
   
   CREATE TABLE MLB_COLL_EXT
	(
	  RES_ID               NUMBER                   NOT NULL,
	  CATEGORY_ID          VARCHAR2(50 BYTE)        ,
	  EXP_CONTACT_ID       INTEGER                  DEFAULT NULL,
	  EXP_USER_ID          VARCHAR2(52 BYTE)        DEFAULT NULL,
	  DEST_CONTACT_ID      INTEGER                  DEFAULT NULL,
	  DEST_USER_ID         VARCHAR2(52 BYTE)        DEFAULT NULL,
	  NATURE_ID            VARCHAR2(50 BYTE),
	  ALT_IDENTIFIER       VARCHAR2(255 BYTE)       DEFAULT NULL,
	  ADMISSION_DATE       DATE,
	  ANSWER_TYPE_BITMASK  VARCHAR2(7 BYTE)         DEFAULT NULL,
	  OTHER_ANSWER_DESC    VARCHAR2(255 BYTE)       DEFAULT NULL,
	  PROCESS_LIMIT_DATE   DATE,
	  PROCESS_NOTES        VARCHAR2(2048 BYTE)       DEFAULT NULL,
	  CLOSING_DATE         DATE,
	  ALARM1_DATE          DATE,
	  ALARM2_DATE          DATE,
	  FLAG_NOTIF           CHAR(1 BYTE)             DEFAULT 'N',
	  FLAG_ALARM1          CHAR(1 BYTE)             DEFAULT 'N',
	  FLAG_ALARM2          CHAR(1 BYTE)             DEFAULT 'N'
	)
	PCTUSED    0
	PCTFREE    10
	INITRANS   1
	MAXTRANS   255
	STORAGE    (
	            INITIAL          64K
	            MINEXTENTS       1
	            MAXEXTENTS       2147483645
	            PCTINCREASE      0
	            BUFFER_POOL      DEFAULT
	           )
	LOGGING 
	NOCOMPRESS 
	NOCACHE
	NOPARALLEL
	MONITORING;
   
   
   CREATE TABLE MLB_DOCTYPE_EXT
	(
	  TYPE_ID        NUMBER                         NOT NULL,
	  PROCESS_DELAY  NUMBER                         DEFAULT '21'                  ,
	  DELAY1         NUMBER                         DEFAULT '14'                  ,
	  DELAY2         NUMBER                         DEFAULT '1'                   
	)
	PCTUSED    0
	PCTFREE    10
	INITRANS   1
	MAXTRANS   255
	STORAGE    (
	            INITIAL          64K
	            MINEXTENTS       1
	            MAXEXTENTS       2147483645
	            PCTINCREASE      0
	            BUFFER_POOL      DEFAULT
	           )
	LOGGING 
	NOCOMPRESS 
	NOCACHE
	NOPARALLEL
	MONITORING;
	
	
	CREATE TABLE DOCTYPES_INDEXES
	(
	  TYPE_ID     NUMBER                            NOT NULL,
	  COLL_ID     VARCHAR2(32 BYTE)                 NOT NULL,
	  FIELD_NAME  VARCHAR2(255 BYTE)                NOT NULL,
	  MANDATORY   CHAR(1 BYTE)                      DEFAULT 'N'                  
	)
	PCTUSED    0
	PCTFREE    10
	INITRANS   1
	MAXTRANS   255
	STORAGE    (
	            INITIAL          64K
	            MINEXTENTS       1
	            MAXEXTENTS       2147483645
	            PCTINCREASE      0
	            BUFFER_POOL      DEFAULT
	           )
	LOGGING 
	NOCOMPRESS 
	NOCACHE
	NOPARALLEL
	MONITORING;
   
   CREATE TABLE SAVED_QUERIES
	(
	  QUERY_ID                NUMBER                NOT NULL,
	  USER_ID                 VARCHAR2(32 BYTE)     DEFAULT NULL,
	  QUERY_NAME              VARCHAR2(255 BYTE)    NOT NULL,
	  CREATION_DATE           DATE          DEFAULT sysdate,
	  CREATED_BY              VARCHAR2(32 BYTE)     ,
	  QUERY_TYPE              VARCHAR2(50 BYTE)     ,
	  QUERY_TXT               CLOB                  ,
	  LAST_MODIFICATION_DATE  DATE
	)
	PCTUSED    0
	PCTFREE    10
	INITRANS   1
	MAXTRANS   255
	STORAGE    (
	            INITIAL          64K
	            MINEXTENTS       1
	            MAXEXTENTS       2147483645
	            PCTINCREASE      0
	            BUFFER_POOL      DEFAULT
	           )
	LOGGING 
	NOCOMPRESS 
	NOCACHE
	NOPARALLEL
	MONITORING;
	   
--------------------------------------------------------
--  DDL for Table RES_X
--------------------------------------------------------

  CREATE TABLE "RES_X" 
   (	"RES_ID" NUMBER, 
	"TITLE" VARCHAR2(255 CHAR), 
	"SUBJECT" VARCHAR2(4000 CHAR), 
	"DESCRIPTION" VARCHAR2(4000), 
	"PUBLISHER" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"CONTRIBUTOR" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"TYPE_ID" NUMBER, 
	"FORMAT" VARCHAR2(50 CHAR), 
	"TYPIST" VARCHAR2(50 CHAR), 
	"CREATION_DATE" DATE, 
	"FULLTEXT_RESULT" VARCHAR2(10 CHAR) DEFAULT NULL, 
	"OCR_RESULT" VARCHAR2(10 CHAR) DEFAULT NULL, 
	"CONVERTER_RESULT" VARCHAR2(10 CHAR) DEFAULT NULL, 
	"AUTHOR" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"AUTHOR_NAME" VARCHAR2(1000 CHAR), 
	"IDENTIFIER" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"SOURCE" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"DOC_LANGUAGE" VARCHAR2(50 CHAR) DEFAULT NULL, 
	"RELATION" NUMBER DEFAULT NULL, 
	"COVERAGE" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"DOC_DATE" DATE DEFAULT NULL, 
	"DOCSERVER_ID" VARCHAR2(32 CHAR), 
	"FOLDERS_SYSTEM_ID" NUMBER DEFAULT NULL, 
	"ARBOX_ID" VARCHAR2(32 CHAR) DEFAULT NULL, 
	"PATH" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"FILENAME" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"OFFSET_DOC" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"LOGICAL_ADR" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"FINGERPRINT" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"FILESIZE" NUMBER DEFAULT NULL, 
	"IS_PAPER" VARCHAR2(1 CHAR) DEFAULT NULL, 
	"PAGE_COUNT" NUMBER DEFAULT NULL, 
	"SCAN_DATE" DATE DEFAULT NULL, 
	"SCAN_USER" VARCHAR2(50 CHAR) DEFAULT NULL, 
	"SCAN_LOCATION" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"SCAN_WKSTATION" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"SCAN_BATCH" VARCHAR2(50 CHAR) DEFAULT NULL, 
	"BURN_BATCH" VARCHAR2(50 CHAR) DEFAULT NULL, 
	"SCAN_POSTMARK" VARCHAR2(50 CHAR) DEFAULT NULL, 
	"ENVELOP_ID" NUMBER DEFAULT NULL, 
	"STATUS" VARCHAR2(10 CHAR) DEFAULT NULL, 
	"DESTINATION" VARCHAR2(50 CHAR) DEFAULT NULL, 
	"APPROVER" VARCHAR2(50 CHAR) DEFAULT NULL, 
	"VALIDATION_DATE" DATE DEFAULT NULL, 
	"WORK_BATCH" NUMBER DEFAULT NULL, 
	"ORIGIN" NUMBER DEFAULT NULL, 
	"IS_INGOING" VARCHAR2(1 CHAR) DEFAULT NULL, 
	"PRIORITY" NUMBER DEFAULT NULL, 
	"ARBATCH_ID" VARCHAR2(32 CHAR) DEFAULT NULL, 
	"CUSTOM_T1" VARCHAR2(1000 CHAR), 
	"CUSTOM_N1" NUMBER DEFAULT NULL, 
	"CUSTOM_F1" NUMBER(*,0) DEFAULT NULL, 
	"CUSTOM_D1" DATE DEFAULT NULL, 
	"CUSTOM_T2" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"CUSTOM_N2" NUMBER DEFAULT NULL, 
	"CUSTOM_F2" NUMBER(*,0) DEFAULT NULL, 
	"CUSTOM_D2" DATE DEFAULT NULL, 
	"CUSTOM_T3" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"CUSTOM_N3" NUMBER DEFAULT NULL, 
	"CUSTOM_F3" NUMBER(*,0) DEFAULT NULL, 
	"CUSTOM_D3" DATE DEFAULT NULL, 
	"CUSTOM_T4" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"CUSTOM_N4" NUMBER DEFAULT NULL, 
	"CUSTOM_F4" NUMBER(*,0) DEFAULT NULL, 
	"CUSTOM_D4" DATE DEFAULT NULL, 
	"CUSTOM_T5" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"CUSTOM_N5" NUMBER DEFAULT NULL, 
	"CUSTOM_F5" NUMBER(*,0) DEFAULT NULL, 
	"CUSTOM_D5" DATE DEFAULT NULL, 
	"CUSTOM_T6" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"CUSTOM_D6" DATE DEFAULT NULL, 
	"CUSTOM_T7" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"CUSTOM_D7" DATE DEFAULT NULL, 
	"CUSTOM_T8" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"CUSTOM_D8" DATE DEFAULT NULL, 
	"CUSTOM_T9" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"CUSTOM_D9" DATE DEFAULT NULL, 
	"CUSTOM_T10" VARCHAR2(255) DEFAULT NULL, 
	"CUSTOM_D10" DATE DEFAULT NULL, 
	"CUSTOM_T11" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"CUSTOM_T12" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"CUSTOM_T13" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"CUSTOM_T14" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"CUSTOM_T15" VARCHAR2(255 CHAR) DEFAULT NULL, 
	"TABLENAME" VARCHAR2(32 CHAR) DEFAULT 'res_x', 
	"INITIATOR" VARCHAR2(50 CHAR) DEFAULT NULL, 
	"DEST_USER" VARCHAR2(50 CHAR) DEFAULT NULL, 
	"VIDEO_BATCH" NUMBER DEFAULT NULL, 
	"VIDEO_TIME" NUMBER DEFAULT NULL, 
	"VIDEO_USER" VARCHAR2(50 CHAR),
	"VIDEO_DATE" DATE DEFAULT NULL
   ) ;
   
--------------------------------------------------------
--  SEQUENCES AND TRIGGERS
--------------------------------------------------------

 CREATE SEQUENCE  "SEQ_DOCTYPES_FIRST_LEVEL"  MINVALUE 1 MAXVALUE 999999999999999999999999999 INCREMENT BY 1 START WITH 1 CACHE 20 NOORDER  NOCYCLE ;
 create or replace TRIGGER TRG_DOCTYPES_FIRST_LEVEL
 BEFORE INSERT ON DOCTYPES_FIRST_LEVEL 
FOR EACH ROW 
BEGIN
  SELECT SEQ_DOCTYPES_FIRST_LEVEL.NEXTVAL INTO :NEW.DOCTYPES_FIRST_LEVEL_ID FROM DUAL;
END;
/
   
 CREATE SEQUENCE  "SEQ_DOCTYPES_SECOND_LEVEL"  MINVALUE 1 MAXVALUE 999999999999999999999999999 INCREMENT BY 1 START WITH 1 CACHE 20 NOORDER  NOCYCLE ;
 create or replace TRIGGER TRG_DOCTYPES_SECOND_LEVEL
 BEFORE INSERT ON DOCTYPES_SECOND_LEVEL 
FOR EACH ROW 
BEGIN
  SELECT SEQ_DOCTYPES_SECOND_LEVEL.NEXTVAL INTO :NEW.DOCTYPES_SECOND_LEVEL_ID FROM DUAL;
END;
/

 CREATE SEQUENCE  "SEQ_RES_X"  MINVALUE 1 MAXVALUE 999999999999999999999999999 INCREMENT BY 1 START WITH 1 CACHE 20 NOORDER  NOCYCLE ;
 create or replace TRIGGER TRG_RES_X
 BEFORE INSERT ON RES_X 
FOR EACH ROW 
BEGIN
  SELECT SEQ_RES_X.NEXTVAL INTO :NEW.RES_ID FROM DUAL;
END;
/



  
--------------------------------------------------------
--  DDL for View RES_VIEW
--------------------------------------------------------

  CREATE OR REPLACE VIEW "RES_VIEW" ("RES_ID", "TITLE", "SUBJECT", "DESCRIPTION", "PUBLISHER", "CONTRIBUTOR", "TYPE_ID", "FORMAT", "TYPIST", "CREATION_DATE", "FULLTEXT_RESULT", "OCR_RESULT", "CONVERTER_RESULT", "AUTHOR", "AUTHOR_NAME", "IDENTIFIER", "SOURCE", "DOC_LANGUAGE", "RELATION", "COVERAGE", "DOC_DATE", "DOCSERVER_ID", "FOLDERS_SYSTEM_ID", "ARBOX_ID", "PATH", "FILENAME", "OFFSET_DOC", "LOGICAL_ADR", "FINGERPRINT", "FILESIZE", "IS_PAPER", "PAGE_COUNT", "SCAN_DATE", "SCAN_USER", "SCAN_LOCATION", "SCAN_WKSTATION", "SCAN_BATCH", "BURN_BATCH", "SCAN_POSTMARK", "ENVELOP_ID", "STATUS", "DESTINATION", "APPROVER", "VALIDATION_DATE", "WORK_BATCH", "ORIGIN", "IS_INGOING", "PRIORITY", "ARBATCH_ID", "DOC_CUSTOM_T1", "DOC_CUSTOM_N1", "DOC_CUSTOM_F1", "DOC_CUSTOM_D1", "DOC_CUSTOM_T2", "DOC_CUSTOM_N2", "DOC_CUSTOM_F2", "DOC_CUSTOM_D2", "DOC_CUSTOM_T3", "DOC_CUSTOM_N3", "DOC_CUSTOM_F3", "DOC_CUSTOM_D3", "DOC_CUSTOM_T4", "DOC_CUSTOM_N4", "DOC_CUSTOM_F4", "DOC_CUSTOM_D4", "DOC_CUSTOM_T5", "DOC_CUSTOM_N5", "DOC_CUSTOM_F5", "DOC_CUSTOM_D5", "DOC_CUSTOM_T6", "DOC_CUSTOM_D6", "DOC_CUSTOM_T7", "DOC_CUSTOM_D7", "DOC_CUSTOM_T8", "DOC_CUSTOM_D8", "DOC_CUSTOM_T9", "DOC_CUSTOM_D9", "DOC_CUSTOM_T10", "DOC_CUSTOM_D10", "DOC_CUSTOM_T11", "DOC_CUSTOM_T12", "DOC_CUSTOM_T13", "DOC_CUSTOM_T14", "DOC_CUSTOM_T15", "TABLENAME", "INITIATOR", "DEST_USER", "VIDEO_BATCH", "VIDEO_TIME", "VIDEO_USER", "COLL_ID", "TYPE_LABEL", "ENABLED", "DOCTYPES_FIRST_LEVEL_ID", "DOCTYPES_SECOND_LEVEL_ID", "RETENTION") AS 
  SELECT 
    RES_X.RES_ID RES_ID, 
    RES_X.TITLE TITLE, 
    RES_X.SUBJECT SUBJECT, 
    RES_X.DESCRIPTION DESCRIPTION, 
    RES_X.PUBLISHER PUBLISHER, 
    RES_X.CONTRIBUTOR CONTRIBUTOR, 
    RES_X.TYPE_ID TYPE_ID, 
    RES_X.FORMAT FORMAT, 
    RES_X.TYPIST TYPIST, 
    RES_X.CREATION_DATE CREATION_DATE, 
    RES_X.FULLTEXT_RESULT FULLTEXT_RESULT, 
    RES_X.OCR_RESULT OCR_RESULT, 
    RES_X.CONVERTER_RESULT CONVERTER_RESULT, 
    RES_X.AUTHOR AUTHOR, 
    RES_X.AUTHOR_NAME AUTHOR_NAME, 
    RES_X.IDENTIFIER IDENTIFIER, 
    RES_X.SOURCE SOURCE, 
    RES_X.DOC_LANGUAGE DOC_LANGUAGE, 
    RES_X.RELATION RELATION, 
    RES_X.COVERAGE COVERAGE, 
    RES_X.DOC_DATE DOC_DATE, 
    RES_X.DOCSERVER_ID DOCSERVER_ID, 
    RES_X.FOLDERS_SYSTEM_ID FOLDERS_SYSTEM_ID, 
    RES_X.ARBOX_ID ARBOX_ID, 
    RES_X.PATH PATH, 
    RES_X.FILENAME FILENAME, 
    RES_X.OFFSET_DOC OFFSET_DOC, 
    RES_X.LOGICAL_ADR LOGICAL_ADR, 
    RES_X.FINGERPRINT FINGERPRINT, 
    RES_X.FILESIZE FILESIZE, 
    RES_X.IS_PAPER IS_PAPER, 
    RES_X.PAGE_COUNT PAGE_COUNT, 
    RES_X.SCAN_DATE SCAN_DATE, 
    RES_X.SCAN_USER SCAN_USER, 
    RES_X.SCAN_LOCATION SCAN_LOCATION, 
    RES_X.SCAN_WKSTATION SCAN_WKSTATION, 
    RES_X.SCAN_BATCH SCAN_BATCH, 
    RES_X.BURN_BATCH BURN_BATCH, 
    RES_X.SCAN_POSTMARK SCAN_POSTMARK, 
    RES_X.ENVELOP_ID ENVELOP_ID, 
    RES_X.STATUS STATUS, 
    RES_X.DESTINATION DESTINATION, 
    RES_X.APPROVER APPROVER, 
    RES_X.VALIDATION_DATE VALIDATION_DATE, 
    RES_X.WORK_BATCH WORK_BATCH, 
    RES_X.ORIGIN ORIGIN, 
    RES_X.IS_INGOING IS_INGOING, 
    RES_X.PRIORITY PRIORITY, 
    RES_X.ARBATCH_ID ARBATCH_ID, 
    RES_X.CUSTOM_T1 DOC_CUSTOM_T1, 
    RES_X.CUSTOM_N1 DOC_CUSTOM_N1, 
    RES_X.CUSTOM_F1 DOC_CUSTOM_F1, 
    RES_X.CUSTOM_D1 DOC_CUSTOM_D1, 
    RES_X.CUSTOM_T2 DOC_CUSTOM_T2, 
    RES_X.CUSTOM_N2 DOC_CUSTOM_N2, 
    RES_X.CUSTOM_F2 DOC_CUSTOM_F2, 
    RES_X.CUSTOM_D2 DOC_CUSTOM_D2, 
    RES_X.CUSTOM_T3 DOC_CUSTOM_T3, 
    RES_X.CUSTOM_N3 DOC_CUSTOM_N3, 
    RES_X.CUSTOM_F3 DOC_CUSTOM_F3, 
    RES_X.CUSTOM_D3 DOC_CUSTOM_D3, 
    RES_X.CUSTOM_T4 DOC_CUSTOM_T4, 
    RES_X.CUSTOM_N4 DOC_CUSTOM_N4, 
    RES_X.CUSTOM_F4 DOC_CUSTOM_F4, 
    RES_X.CUSTOM_D4 DOC_CUSTOM_D4, 
    RES_X.CUSTOM_T5 DOC_CUSTOM_T5, 
    RES_X.CUSTOM_N5 DOC_CUSTOM_N5, 
    RES_X.CUSTOM_F5 DOC_CUSTOM_F5, 
    RES_X.CUSTOM_D5 DOC_CUSTOM_D5, 
    RES_X.CUSTOM_T6 DOC_CUSTOM_T6, 
    RES_X.CUSTOM_D6 DOC_CUSTOM_D6, 
    RES_X.CUSTOM_T7 DOC_CUSTOM_T7, 
    RES_X.CUSTOM_D7 DOC_CUSTOM_D7, 
    RES_X.CUSTOM_T8 DOC_CUSTOM_T8, 
    RES_X.CUSTOM_D8 DOC_CUSTOM_D8, 
    RES_X.CUSTOM_T9 DOC_CUSTOM_T9, 
    RES_X.CUSTOM_D9 DOC_CUSTOM_D9, 
    RES_X.CUSTOM_T10 DOC_CUSTOM_T10, 
    RES_X.CUSTOM_D10 DOC_CUSTOM_D10, 
    RES_X.CUSTOM_T11 DOC_CUSTOM_T11, 
    RES_X.CUSTOM_T12 DOC_CUSTOM_T12, 
    RES_X.CUSTOM_T13 DOC_CUSTOM_T13, 
    RES_X.CUSTOM_T14 DOC_CUSTOM_T14, 
    RES_X.CUSTOM_T15 DOC_CUSTOM_T15, 
    RES_X.TABLENAME TABLENAME, 
    RES_X.INITIATOR INITIATOR, 
    RES_X.DEST_USER DEST_USER, 
    RES_X.VIDEO_BATCH VIDEO_BATCH, 
    RES_X.VIDEO_TIME VIDEO_TIME, 
    RES_X.VIDEO_USER VIDEO_USER, 
    DOCTYPES.COLL_ID COLL_ID, 
    DOCTYPES.DESCRIPTION TYPE_LABEL, 
    DOCTYPES.ENABLED ENABLED, 
    DOCTYPES.DOCTYPES_FIRST_LEVEL_ID DOCTYPES_FIRST_LEVEL_ID, 
    DOCTYPES.DOCTYPES_SECOND_LEVEL_ID DOCTYPES_SECOND_LEVEL_ID, 
    DOCTYPES.RETENTION RETENTION 
FROM 
    RES_X, 
    DOCTYPES, 
    DOCTYPES_FIRST_LEVEL, 
    DOCTYPES_SECOND_LEVEL 
WHERE 
    DOCTYPES.TYPE_ID = RES_X.TYPE_ID AND DOCTYPES.DOCTYPES_FIRST_LEVEL_ID = DOCTYPES_FIRST_LEVEL.DOCTYPES_FIRST_LEVEL_ID AND DOCTYPES.DOCTYPES_SECOND_LEVEL_ID = DOCTYPES_SECOND_LEVEL.DOCTYPES_SECOND_LEVEL_ID;
 
 
 
 
