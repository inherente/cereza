BEGIN
    DECLARE conflict_count INT;
    DECLARE found_id INT;

 -- Check if there is already an event created for given calendar_id

 -- Check for conflicting events
    SELECT COUNT(*) INTO conflict_count
    FROM FINE_EVENT
    WHERE ((NEW.start_date >= start_date AND NEW.start_date < end_date)
        OR (NEW.end_date > start_date AND NEW.end_date <= end_date)
        OR (NEW.start_date < start_date AND NEW.end_date > end_date));

 -- If there is a conflict, signal an error
    IF conflict_count > 0 THEN
    SIGNAL SQLSTATE '45000' 
    SET MESSAGE_TEXT = 'Conflict with an existing event in the given time interval.';
    END IF;

 -- Move existing record with the same calendar_id to the history table
    IF EXISTS (SELECT 1 FROM FINE_EVENT WHERE calendar_id = NEW.calendar_id) THEN
    INSERT INTO FINE_EVENT_HISTORY (
        id,
        calendar_id,
        kind,
        status,
        creator_id,
        organizer_id,
        start_date,
        end_date,
        created_date,
        updated_date,
        summary
    )
    SELECT
        id,
        calendar_id,
        kind,
        status,
        creator_id,
        organizer_id,
        start_date,
        end_date,
        created_date,
        updated_date,
        summary
    FROM FINE_EVENT
    WHERE calendar_id = NEW.calendar_id;
 -- UPDATE FINE_EVENT SET control= 'to-be-deleted' WHERE calendar_id = NEW.calendar_id;

 -- Delete the existing record from FINE_EVENT
 -- DELETE FROM FINE_EVENT WHERE calendar_id = NEW.calendar_id;
    INSERT INTO FINE_EVENT_TRANSITION (calendar_id) VALUES(NEW.calendar_id);
    END IF;


END