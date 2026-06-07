# Процедура №1

CREATE OR REPLACE PROCEDURE add_stock(
    IN p_establishment_adress VARCHAR(100),
    IN p_product_article SMALLINT,
    IN p_quantity SMALLINT
)
LANGUAGE plpgsql
AS $$
BEGIN
    INSERT INTO containing (establishment_adress, product_article, containing_num)
    VALUES (p_establishment_adress, p_product_article, p_quantity)
    ON CONFLICT (establishment_adress, product_article)
    DO UPDATE SET containing_num = containing.containing_num + EXCLUDED.containing_num;
    
    RAISE NOTICE 'Остатки успешно обновлены';
    
EXCEPTION
    WHEN foreign_key_violation THEN
        RAISE NOTICE 'Ошибка: Проверьте существование заведения или товара';
END;
$$;

# Триггер №1

CREATE OR REPLACE FUNCTION stock_update()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    UPDATE containing c
    SET containing_num = c.containing_num - NEW.bill_content_count
    FROM bill b
    WHERE b.bill_id = NEW.bill_id
      AND c.establishment_adress = b.establishment_adress
      AND c.product_article = NEW.product_article;
    
    RETURN NEW;
END;
$$;

# Триггер №2

CREATE TRIGGER bought_update
AFTER INSERT ON bill_content
FOR EACH ROW
EXECUTE FUNCTION stock_update();

CREATE OR REPLACE FUNCTION set_delivered()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    IF NEW.supply_state = 2 AND (OLD.supply_state IS NULL OR OLD.supply_state != 2) THEN
        NEW.supply_date_recieved := CURRENT_DATE;
    END IF;
    
    RETURN NEW;
END;
$$;

CREATE TRIGGER update_delivery
BEFORE UPDATE ON supply
FOR EACH ROW
EXECUTE FUNCTION set_delivered();

