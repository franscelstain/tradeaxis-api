-- Purpose:
-- Enforce append-only / immutable behavior for production-grade history tables.
--
-- CORRECTION 2026-08-06 (W10). The previous form of this file blocked every UPDATE and DELETE on
-- the history tables unconditionally. That is stricter than the policy it exists to enforce, and
-- strictly incompatible with it: `Canonical_Row_History_and_Versioning_Policy_LOCKED.md` rule 7
-- has a snapshot set "appended/frozen atomically with the seal/publication transition", which
-- means the set is assembled while its publication is still a candidate, and rule 9 forbids
-- update/delete of *sealed* snapshot content specifically. A blanket guard forbids the assembly
-- rule 7 requires, so deploying this file as written would have broken candidate build and every
-- correction flow.
--
-- That mismatch is the likely reason it was never deployed: `information_schema.TRIGGERS` carried
-- zero of these six while the watchlist domain carried fourteen of its own. The result was that
-- 56,138,923 history rows had no database-level protection at all, and the strictest-sounding file
-- in the repository was the one enforcing nothing.
--
-- These guards are therefore seal-aware: they refuse exactly what rule 9 refuses, and permit
-- exactly what rule 7 requires. A row whose publication is already gone cannot be sealed, so the
-- lookup returning nothing is treated as permitted rather than as an error.

DELIMITER $$

CREATE TRIGGER trg_no_update_eod_bars_history
BEFORE UPDATE ON eod_bars_history
FOR EACH ROW
BEGIN
    DECLARE v_seal_state VARCHAR(32);
    SELECT seal_state INTO v_seal_state FROM eod_publications WHERE publication_id = OLD.publication_id;
    IF v_seal_state = 'SEALED' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'eod_bars_history snapshot of a sealed publication is immutable and cannot be updated';
    END IF;
END$$

CREATE TRIGGER trg_no_delete_eod_bars_history
BEFORE DELETE ON eod_bars_history
FOR EACH ROW
BEGIN
    DECLARE v_seal_state VARCHAR(32);
    SELECT seal_state INTO v_seal_state FROM eod_publications WHERE publication_id = OLD.publication_id;
    IF v_seal_state = 'SEALED' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'eod_bars_history snapshot of a sealed publication is immutable and cannot be deleted';
    END IF;
END$$

CREATE TRIGGER trg_no_update_eod_indicators_history
BEFORE UPDATE ON eod_indicators_history
FOR EACH ROW
BEGIN
    DECLARE v_seal_state VARCHAR(32);
    SELECT seal_state INTO v_seal_state FROM eod_publications WHERE publication_id = OLD.publication_id;
    IF v_seal_state = 'SEALED' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'eod_indicators_history snapshot of a sealed publication is immutable and cannot be updated';
    END IF;
END$$

CREATE TRIGGER trg_no_delete_eod_indicators_history
BEFORE DELETE ON eod_indicators_history
FOR EACH ROW
BEGIN
    DECLARE v_seal_state VARCHAR(32);
    SELECT seal_state INTO v_seal_state FROM eod_publications WHERE publication_id = OLD.publication_id;
    IF v_seal_state = 'SEALED' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'eod_indicators_history snapshot of a sealed publication is immutable and cannot be deleted';
    END IF;
END$$

CREATE TRIGGER trg_no_update_eod_eligibility_history
BEFORE UPDATE ON eod_eligibility_history
FOR EACH ROW
BEGIN
    DECLARE v_seal_state VARCHAR(32);
    SELECT seal_state INTO v_seal_state FROM eod_publications WHERE publication_id = OLD.publication_id;
    IF v_seal_state = 'SEALED' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'eod_eligibility_history snapshot of a sealed publication is immutable and cannot be updated';
    END IF;
END$$

CREATE TRIGGER trg_no_delete_eod_eligibility_history
BEFORE DELETE ON eod_eligibility_history
FOR EACH ROW
BEGIN
    DECLARE v_seal_state VARCHAR(32);
    SELECT seal_state INTO v_seal_state FROM eod_publications WHERE publication_id = OLD.publication_id;
    IF v_seal_state = 'SEALED' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'eod_eligibility_history snapshot of a sealed publication is immutable and cannot be deleted';
    END IF;
END$$

DELIMITER ;

-- LOCKED SEMANTICS
-- 1. History tables are append-only snapshot tables once their publication is sealed.
-- 2. A sealed publication's snapshot may never be updated in place or deleted, by any path:
--    application code, repair, recompute, migration, or operator command.
-- 3. A candidate publication's snapshot set is still being assembled and may be rewritten until
--    the seal transition freezes it. This is rule 7 of the history policy, not an exception to it.
-- 4. A correction produces a new publication and a new snapshot set. It never edits the prior one.
-- 5. If exceptional maintenance is ever needed, it must be handled outside normal production flow
--    and versioned/documented explicitly.
