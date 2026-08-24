ALTER TABLE narudzbe
    ADD COLUMN dostava_lat DECIMAL(10,7) DEFAULT NULL AFTER adresa_dostave,
    ADD COLUMN dostava_lng DECIMAL(10,7) DEFAULT NULL AFTER dostava_lat;
