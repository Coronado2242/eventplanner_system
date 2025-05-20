CREATE TABLE proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department VARCHAR(255),
    event_type VARCHAR(255),
    start_date DATE,
    end_date DATE,
    venue VARCHAR(255),
    time VARCHAR(255),
    adviser_form VARCHAR(255),
    certification VARCHAR(255),
    financial VARCHAR(255),
    constitution VARCHAR(255),
    reports VARCHAR(255),
    letter_attachment VARCHAR(255),
    status VARCHAR(50)
);
