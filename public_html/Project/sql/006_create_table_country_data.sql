-- UCID: wg99 | Date: 2025-07-21
-- Table for storing CountryWise API and manual country data
CREATE TABLE IF NOT EXISTS country_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    api_id VARCHAR(64), -- API identifier if available
    name VARCHAR(128) NOT NULL,
    capital VARCHAR(128),
    flag TEXT, -- URL to flag image
    population INT,
    currency VARCHAR(64),
    languages VARCHAR(256),
    continent VARCHAR(64),
    is_api BOOLEAN DEFAULT 0 -- 1 if from API, 0 if manual
);
