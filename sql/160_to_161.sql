-- *************************************************************************--
--                                                                          --
--                                                                          --
--        THIS SCRIPT IS USE TO PASS FROM MAARCH 1.5 TO MAARCH 1.5.1        --
--                                                                          --
--                                                                          --
-- *************************************************************************--
CREATE FUNCTION order_alphanum(text) RETURNS text AS $$
  SELECT regexp_replace(regexp_replace(regexp_replace(regexp_replace($1,
    E'(^|\\D)(\\d{1,3}($|\\D))', E'\\1000\\2', 'g'),
      E'(^|\\D)(\\d{4,6}($|\\D))', E'\\1000\\2', 'g'),
        E'(^|\\D)(\\d{7}($|\\D))', E'\\100\\2', 'g'),
          E'(^|\\D)(\\d{8}($|\\D))', E'\\10\\2', 'g');
$$ LANGUAGE SQL;