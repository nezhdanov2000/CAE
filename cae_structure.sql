-- Студенты
CREATE TABLE Student (
    Student_ID INT PRIMARY KEY AUTO_INCREMENT,
    Email VARCHAR(100) UNIQUE,
    Password VARCHAR(100),
    Name VARCHAR(100),
    Surname VARCHAR(100)
);

-- Курсы
CREATE TABLE Course (
    Course_ID INT PRIMARY KEY AUTO_INCREMENT,
    Course_name VARCHAR(100)
);

-- Преподаватели
CREATE TABLE Tutor (
    Tutor_ID INT PRIMARY KEY AUTO_INCREMENT,
    Email VARCHAR(100) UNIQUE,
    Password VARCHAR(100),
    Name VARCHAR(100),
    Surname VARCHAR(100),
    Student_ID_opt INT,
    FOREIGN KEY (Student_ID_opt) REFERENCES Student(Student_ID)
);

-- Связь студент-курс (многие ко многим)
CREATE TABLE Studying (
    Student_ID INT,
    Course_ID INT,
    PRIMARY KEY (Student_ID, Course_ID),
    FOREIGN KEY (Student_ID) REFERENCES Student(Student_ID),
    FOREIGN KEY (Course_ID) REFERENCES Course(Course_ID)
);

-- Связь преподаватель-курс (многие ко многим)
CREATE TABLE Tutoring (
    Tutor_ID INT,
    Course_ID INT,
    PRIMARY KEY (Tutor_ID, Course_ID),
    FOREIGN KEY (Tutor_ID) REFERENCES Tutor(Tutor_ID),
    FOREIGN KEY (Course_ID) REFERENCES Course(Course_ID)
);

-- Таймслоты (привязаны к курсу)
CREATE TABLE Timeslot (
    Timeslot_ID INT PRIMARY KEY AUTO_INCREMENT,
    Course_ID INT,
    Date DATE,
    Start_Time TIME,
    End_Time TIME,
    FOREIGN KEY (Course_ID) REFERENCES Course(Course_ID)
);

-- Преподаватель создает таймслот
CREATE TABLE Tutor_Creates (
    Tutor_ID INT,
    Timeslot_ID INT,
    PRIMARY KEY (Tutor_ID, Timeslot_ID),
    FOREIGN KEY (Tutor_ID) REFERENCES Tutor(Tutor_ID),
    FOREIGN KEY (Timeslot_ID) REFERENCES Timeslot(Timeslot_ID)
);

-- Студент выбирает таймслот
CREATE TABLE Student_Choice (
    Student_ID INT,
    Timeslot_ID INT,
    PRIMARY KEY (Student_ID, Timeslot_ID),
    FOREIGN KEY (Student_ID) REFERENCES Student(Student_ID),
    FOREIGN KEY (Timeslot_ID) REFERENCES Timeslot(Timeslot_ID)
);

-- Студент участвует в "группе" с абстрактным ID
CREATE TABLE Student_Join (
    Student_ID INT,
    Group_ID INT,
    PRIMARY KEY (Student_ID, Group_ID),
    FOREIGN KEY (Student_ID) REFERENCES Student(Student_ID)
    -- Group_ID — абстрактный, без внешнего ключа
);

-- Запись на занятие
CREATE TABLE Appointment (
    Appointment_ID INT PRIMARY KEY AUTO_INCREMENT,
    Tutor_ID INT,
    Group_ID INT,
    Timeslot_ID INT,
    Location VARCHAR(100),
    FOREIGN KEY (Tutor_ID) REFERENCES Tutor(Tutor_ID),
    FOREIGN KEY (Timeslot_ID) REFERENCES Timeslot(Timeslot_ID)
    -- Group_ID — абстрактный, без внешнего ключа
);
