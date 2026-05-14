ALTER TABLE messages
    ADD COLUMN receiver_id INT NULL AFTER sender_id;

UPDATE messages
SET receiver_id = sender_id
WHERE receiver_id IS NULL;

ALTER TABLE messages
    MODIFY receiver_id INT NOT NULL,
    ADD CONSTRAINT fk_messages_receiver
        FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE;

CREATE INDEX idx_messages_conversation ON messages(sender_id, receiver_id, id);
