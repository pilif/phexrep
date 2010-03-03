-- these are just the mandatory fields. Whatever other field
-- you add directly to the table will be displayed directly
-- in the generic error information.

-- error_info is a PHP serialize()d array. Its members are
-- merged with the fields in the table.
--
-- error_info must contain a few fields:
--     callstack (as determined by calling Exception::getTrace())
--     file (Exception::getFile())
--     line (Exception::getLine())
create table exception_logging(
    id bigserial not null primary key,
    uri text not null,
    type text not null,
    ts timestamp not null default current_timestamp,
    message text not null,
    error_info text not null
);
create index idx_el_ts on exception_logging(ts);