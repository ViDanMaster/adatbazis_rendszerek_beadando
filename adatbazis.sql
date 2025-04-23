CREATE TABLE Users (
    user_id NUMBER PRIMARY KEY,
    username VARCHAR2(50) UNIQUE NOT NULL,
    email VARCHAR2(100) UNIQUE NOT NULL,
    password VARCHAR2(255) NOT NULL,
    profile_picture VARCHAR2(255)
);

CREATE TABLE Libraries (
    library_id NUMBER PRIMARY KEY,
    user_id NUMBER NOT NULL,
    name VARCHAR2(100) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE TABLE Documents (
    document_id NUMBER PRIMARY KEY,
    user_id NUMBER NOT NULL,
    library_id NUMBER,
    name VARCHAR2(255) NOT NULL,
    file_path VARCHAR2(255),
    file_type VARCHAR2(100),
    file_size NUMBER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (library_id) REFERENCES Libraries(library_id) ON DELETE CASCADE
);


CREATE TABLE DocumentShares (
    share_id NUMBER PRIMARY KEY,
    user_id NUMBER NOT NULL,
    document_id NUMBER NOT NULL,
    permission VARCHAR2(20) DEFAULT 'read',
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (document_id) REFERENCES Documents(document_id) ON DELETE CASCADE
);

CREATE TABLE ParentLibraries (
    parent_id NUMBER PRIMARY KEY,
    library_id NUMBER NOT NULL,
    parent_library_id NUMBER,
    FOREIGN KEY (library_id) REFERENCES Libraries(library_id) ON DELETE CASCADE,
    FOREIGN KEY (parent_library_id) REFERENCES Libraries(library_id) ON DELETE CASCADE
);

CREATE TABLE ChildLibraries (
    child_id NUMBER PRIMARY KEY,
    library_id NUMBER NOT NULL,
    child_library_id NUMBER NOT NULL,
    FOREIGN KEY (library_id) REFERENCES Libraries(library_id) ON DELETE CASCADE,
    FOREIGN KEY (child_library_id) REFERENCES Libraries(library_id) ON DELETE CASCADE
);

CREATE TABLE LibraryShares (
    share_id NUMBER PRIMARY KEY,
    user_id NUMBER NOT NULL,
    library_id NUMBER NOT NULL,
    permission VARCHAR2(20) DEFAULT 'read',
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (library_id) REFERENCES Libraries(library_id) ON DELETE CASCADE
);

CREATE TABLE UserGroups (
    group_id NUMBER PRIMARY KEY,
    group_name VARCHAR2(100) NOT NULL,
    user_id NUMBER NOT NULL,
    description CLOB,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE TABLE UserGroupMembers (
    member_id NUMBER PRIMARY KEY,
    user_id NUMBER NOT NULL,
    group_id NUMBER NOT NULL,
    role VARCHAR2(50) DEFAULT 'member',
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES UserGroups(group_id) ON DELETE CASCADE
);

CREATE TABLE Calendars (
    calendar_id NUMBER PRIMARY KEY,
    user_id NUMBER NOT NULL,
    name VARCHAR2(100) NOT NULL,
    color VARCHAR2(20) DEFAULT '#3498db',
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE TABLE Events (
    event_id NUMBER PRIMARY KEY,
    user_id NUMBER NOT NULL,
    calendar_id NUMBER NOT NULL,
    title VARCHAR2(255) NOT NULL,
    description CLOB,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NOT NULL,
    location VARCHAR2(255),
    is_recurring VARCHAR2(20) DEFAULT 'FALSE',
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (calendar_id) REFERENCES Calendars(calendar_id) ON DELETE CASCADE
);

CREATE TABLE CalendarShares (
    share_id NUMBER PRIMARY KEY,
    calendar_id NUMBER NOT NULL,
    user_id NUMBER NOT NULL,
    permission VARCHAR2(20) DEFAULT 'read',
    FOREIGN KEY (calendar_id) REFERENCES Calendars(calendar_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE TABLE EventDocuments (
    link_id NUMBER PRIMARY KEY,
    document_id NUMBER NOT NULL,
    event_id NUMBER NOT NULL,
    FOREIGN KEY (document_id) REFERENCES Documents(document_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES Events(event_id) ON DELETE CASCADE
);

CREATE SEQUENCE LIBRARY_SEQ
  START WITH 1
  INCREMENT BY 1
  NOCACHE
  NOCYCLE;

CREATE SEQUENCE DOCUMENT_SEQ
  START WITH 1
  INCREMENT BY 1
  NOCACHE
  NOCYCLE;
  
CREATE SEQUENCE PARENTLIB_SEQ START WITH 6 INCREMENT BY 1 NOCACHE NOCYCLE;
CREATE SEQUENCE CHILDLIB_SEQ START WITH 6 INCREMENT BY 1 NOCACHE NOCYCLE;
CREATE SEQUENCE LIBSHARE_SEQ START WITH 6 INCREMENT BY 1 NOCACHE NOCYCLE;
CREATE SEQUENCE USER_SEQ START WITH 6 INCREMENT BY 1 NOCACHE NOCYCLE;
CREATE SEQUENCE DOCSHARE_SEQ START WITH 6 INCREMENT BY 1 NOCACHE NOCYCLE;
CREATE SEQUENCE EVENT_SEQ START WITH 6 INCREMENT BY 1 NOCACHE NOCYCLE;
CREATE SEQUENCE CALENDAR_SEQ START WITH 6 INCREMENT BY 1 NOCACHE NOCYCLE;
CREATE SEQUENCE CALSHARE_SEQ START WITH 6 INCREMENT BY 1 NOCACHE NOCYCLE;
CREATE SEQUENCE USERGROUP_SEQ START WITH 6 INCREMENT BY 1 NOCACHE NOCYCLE;
CREATE SEQUENCE GROUPMEMBER_SEQ START WITH 6 INCREMENT BY 1 NOCACHE NOCYCLE;
CREATE SEQUENCE EVENTDOC_SEQ START WITH 6 INCREMENT BY 1 NOCACHE NOCYCLE;

INSERT INTO Users (user_id, username, email, password, profile_picture) VALUES
(1, 'user1', 'user1@example.com', 'hashedpassword1', 'pic1.jpg'),
(2, 'user2', 'user2@example.com', 'hashedpassword2', 'pic2.jpg'),
(3, 'user3', 'user3@example.com', 'hashedpassword3', 'pic3.jpg'),
(4, 'user4', 'user4@example.com', 'hashedpassword4', 'pic4.jpg'),
(5, 'user5', 'user5@example.com', 'hashedpassword5', 'pic5.jpg');

/*INSERT INTO Libraries (library_id, user_id, name) VALUES
(1, 1, 'Library 1'),
(2, 2, 'Library 2'),
(3, 3, 'Library 3'),
(4, 4, 'Library 4'),
(5, 5, 'Library 5');

INSERT INTO Documents (document_id, user_id, library_id, name, file_path, file_type, file_size, created_at, updated_at) VALUES
(1, 1, 1, 'Document 1', 'uploads/user_1/doc1.pdf', 'application/pdf', 1024, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 2, 2, 'Document 2', 'uploads/user_2/doc2.docx', 'application/docx', 2048, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, 3, 3, 'Document 3', 'uploads/user_3/doc3.txt', 'text/plain', 512, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(4, 4, 4, 'Document 4', 'uploads/user_4/doc4.xlsx', 'application/xlsx', 3072, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(5, 5, 5, 'Document 5', 'uploads/user_5/doc5.pptx', 'application/pptx', 4096, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT INTO DocumentShares (share_id, user_id, document_id, permission) VALUES
(1, 2, 1, 'read'),
(2, 3, 2, 'edit'),
(3, 4, 3, 'read'),
(4, 5, 4, 'edit'),
(5, 1, 5, 'read');

INSERT INTO LibraryShares (share_id, user_id, library_id, permission) VALUES
(1, 2, 1, 'read'),
(2, 3, 2, 'edit'),
(3, 4, 3, 'read'),
(4, 5, 4, 'edit'),
(5, 1, 5, 'read');

INSERT INTO UserGroups (group_id, group_name, user_id, description) VALUES
(1, 'Group 1', 1, 'Description 1'),
(2, 'Group 2', 2, 'Description 2'),
(3, 'Group 3', 3, 'Description 3'),
(4, 'Group 4', 4, 'Description 4'),
(5, 'Group 5', 5, 'Description 5');

INSERT INTO UserGroupMembers (member_id, user_id, group_id, role) VALUES
(1, 2, 1, 'member'),
(2, 3, 2, 'admin'),
(3, 4, 3, 'member'),
(4, 5, 4, 'admin'),
(5, 1, 5, 'member');

INSERT INTO Calendars (calendar_id, user_id, name, color) VALUES
(1, 1, 'Calendar 1', '#ff0000'),
(2, 2, 'Calendar 2', '#00ff00'),
(3, 3, 'Calendar 3', '#0000ff'),
(4, 4, 'Calendar 4', '#ffff00'),
(5, 5, 'Calendar 5', '#ff00ff');

INSERT INTO Events (event_id, user_id, calendar_id, title, description, start_time, end_time, location, is_recurring) VALUES
(1, 1, 1, 'Event 1', 'Description 1', TIMESTAMP '2024-06-01 10:00:00', TIMESTAMP '2024-06-01 12:00:00', 'Location 1', 'FALSE'),
(2, 2, 2, 'Event 2', 'Description 2', TIMESTAMP '2024-06-02 11:00:00', TIMESTAMP '2024-06-02 13:00:00', 'Location 2', 'TRUE'),
(3, 3, 3, 'Event 3', 'Description 3', TIMESTAMP '2024-06-03 12:00:00', TIMESTAMP '2024-06-03 14:00:00', 'Location 3', 'FALSE'),
(4, 4, 4, 'Event 4', 'Description 4', TIMESTAMP '2024-06-04 13:00:00', TIMESTAMP '2024-06-04 15:00:00', 'Location 4', 'TRUE'),
(5, 5, 5, 'Event 5', 'Description 5', TIMESTAMP '2024-06-05 14:00:00', TIMESTAMP '2024-06-05 16:00:00', 'Location 5', 'FALSE');

INSERT INTO CalendarShares (share_id, calendar_id, user_id, permission) VALUES
(1, 1, 2, 'read'),
(2, 2, 3, 'edit'),
(3, 3, 4, 'read'),
(4, 4, 5, 'edit'),
(5, 5, 1, 'read');

INSERT INTO EventDocuments (link_id, document_id, event_id) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 3),
(4, 4, 4),
(5, 5, 5);

INSERT INTO ParentLibraries (parent_id, library_id, parent_library_id) VALUES
(1, 1, NULL),
(2, 2, 1),
(3, 3, 2),
(4, 4, 3),
(5, 5, 4);

INSERT INTO ChildLibraries (child_id, library_id, child_library_id) VALUES
(1, 1, 2),
(2, 2, 3),
(3, 3, 4),
(4, 4, 5);*/