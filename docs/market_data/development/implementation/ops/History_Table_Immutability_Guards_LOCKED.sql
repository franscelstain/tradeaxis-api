-- MD-B10-A001 operational reference for the database-level sealed-publication history guards.
-- Canonical deployment path: database/migrations/2026_08_24_000001_enforce_sealed_history_and_projection_reconciliation.php
-- Canonical rejection semantic: SEALED_PUBLICATION_IMMUTABLE
--
-- Candidate history may be assembled while the publication is not sealed. Once seal_state=SEALED,
-- INSERT, UPDATE and DELETE are all refused. UPDATE checks both OLD and NEW publication bindings so
-- a row cannot be moved into or out of a sealed publication.

DELIMITER $$

CREATE TRIGGER trg_eod_bars_history_bi_sealed_immutable BEFORE INSERT ON eod_bars_history FOR EACH ROW
BEGIN
    IF EXISTS (SELECT 1 FROM eod_publications WHERE publication_id = NEW.publication_id AND seal_state = 'SEALED') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SEALED_PUBLICATION_IMMUTABLE';
    END IF;
END$$
CREATE TRIGGER trg_eod_bars_history_bu_sealed_immutable BEFORE UPDATE ON eod_bars_history FOR EACH ROW
BEGIN
    IF EXISTS (SELECT 1 FROM eod_publications WHERE publication_id = OLD.publication_id AND seal_state = 'SEALED')
       OR EXISTS (SELECT 1 FROM eod_publications WHERE publication_id = NEW.publication_id AND seal_state = 'SEALED') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SEALED_PUBLICATION_IMMUTABLE';
    END IF;
END$$
CREATE TRIGGER trg_eod_bars_history_bd_sealed_immutable BEFORE DELETE ON eod_bars_history FOR EACH ROW
BEGIN
    IF EXISTS (SELECT 1 FROM eod_publications WHERE publication_id = OLD.publication_id AND seal_state = 'SEALED') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SEALED_PUBLICATION_IMMUTABLE';
    END IF;
END$$

CREATE TRIGGER trg_eod_indicators_history_bi_sealed_immutable BEFORE INSERT ON eod_indicators_history FOR EACH ROW
BEGIN
    IF EXISTS (SELECT 1 FROM eod_publications WHERE publication_id = NEW.publication_id AND seal_state = 'SEALED') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SEALED_PUBLICATION_IMMUTABLE';
    END IF;
END$$
CREATE TRIGGER trg_eod_indicators_history_bu_sealed_immutable BEFORE UPDATE ON eod_indicators_history FOR EACH ROW
BEGIN
    IF EXISTS (SELECT 1 FROM eod_publications WHERE publication_id = OLD.publication_id AND seal_state = 'SEALED')
       OR EXISTS (SELECT 1 FROM eod_publications WHERE publication_id = NEW.publication_id AND seal_state = 'SEALED') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SEALED_PUBLICATION_IMMUTABLE';
    END IF;
END$$
CREATE TRIGGER trg_eod_indicators_history_bd_sealed_immutable BEFORE DELETE ON eod_indicators_history FOR EACH ROW
BEGIN
    IF EXISTS (SELECT 1 FROM eod_publications WHERE publication_id = OLD.publication_id AND seal_state = 'SEALED') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SEALED_PUBLICATION_IMMUTABLE';
    END IF;
END$$

CREATE TRIGGER trg_eod_eligibility_history_bi_sealed_immutable BEFORE INSERT ON eod_eligibility_history FOR EACH ROW
BEGIN
    IF EXISTS (SELECT 1 FROM eod_publications WHERE publication_id = NEW.publication_id AND seal_state = 'SEALED') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SEALED_PUBLICATION_IMMUTABLE';
    END IF;
END$$
CREATE TRIGGER trg_eod_eligibility_history_bu_sealed_immutable BEFORE UPDATE ON eod_eligibility_history FOR EACH ROW
BEGIN
    IF EXISTS (SELECT 1 FROM eod_publications WHERE publication_id = OLD.publication_id AND seal_state = 'SEALED')
       OR EXISTS (SELECT 1 FROM eod_publications WHERE publication_id = NEW.publication_id AND seal_state = 'SEALED') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SEALED_PUBLICATION_IMMUTABLE';
    END IF;
END$$
CREATE TRIGGER trg_eod_eligibility_history_bd_sealed_immutable BEFORE DELETE ON eod_eligibility_history FOR EACH ROW
BEGIN
    IF EXISTS (SELECT 1 FROM eod_publications WHERE publication_id = OLD.publication_id AND seal_state = 'SEALED') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SEALED_PUBLICATION_IMMUTABLE';
    END IF;
END$$

DELIMITER ;

-- LOCKED IMPLEMENTATION SEMANTICS
-- 1. Nine guards are required: INSERT/UPDATE/DELETE × bars/indicators/eligibility history.
-- 2. Snapshot assembly is permitted only while the publication is a candidate/unsealed.
-- 3. After seal, no SQL client may add, rewrite, move, or delete history content.
-- 4. Corrections create a new publication/snapshot and never mutate the sealed predecessor.
