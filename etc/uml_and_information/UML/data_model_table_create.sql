CREATE SCHEMA ticket_system;

CREATE TABLE ticket_system.employees (
    id_employe SERIAL NOT NULL,
    first_name varchar(256) NOT NULL,
    last_name varchar(256) NOT NULL,
    email varchar(256) NOT NULL,
    status varchar(20) NOT NULL,
    password text NOT NULL,
    role varchar(20) NOT NULL,
    create_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    update_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    token text,
    PRIMARY KEY (id_employe)
);

ALTER TABLE ticket_system.employees
    ADD UNIQUE (email);


CREATE TABLE ticket_system.tickets (
    id_ticket SERIAL NOT NULL,
    subject varchar(256) NOT NULL,
    id_employe integer NOT NULL,
    status varchar(20) NOT NULL,
    description text NOT NULL,
    create_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    update_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_ticket)
);

CREATE INDEX ON ticket_system.tickets
    (id_employe);


CREATE TABLE ticket_system.times_entries (
    id_time_entry SERIAL NOT NULL,
    id_employe integer NOT NULL,
    id_ticket integer NOT NULL,
    from_date timestamp without time zone NOT NULL,
    to_date timestamp without time zone NOT NULL,
    note text NOT NULL,
    create_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    update_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_time_entry)
);

CREATE INDEX ON ticket_system.times_entries
    (id_employe);
CREATE INDEX ON ticket_system.times_entries
    (id_ticket);


ALTER TABLE ticket_system.tickets ADD CONSTRAINT FK_tickets__id_employe FOREIGN KEY (id_employe) REFERENCES ticket_system.employees(id_employe);
ALTER TABLE ticket_system.times_entries ADD CONSTRAINT FK_times_entries__id_employe FOREIGN KEY (id_employe) REFERENCES ticket_system.employees(id_employe);
ALTER TABLE ticket_system.times_entries ADD CONSTRAINT FK_times_entries__id_ticket FOREIGN KEY (id_ticket) REFERENCES ticket_system.tickets(id_ticket);