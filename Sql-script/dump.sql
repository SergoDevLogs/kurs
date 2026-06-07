--
-- PostgreSQL database dump
--

\restrict IUP1aDYd4EAlF3Fm1E6lvHJsoKeRC9kNVERLKQYXAKGJXX5RHpB0qKj50a2kMf6

-- Dumped from database version 15.14
-- Dumped by pg_dump version 18.1

-- Started on 2026-04-28 14:02:52

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 232 (class 1255 OID 17638)
-- Name: add_stock(character varying, smallint, smallint); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.add_stock(IN add_establishment_adress character varying, IN add_product_article smallint, IN add_quantity smallint)
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Пытаемся добавить запись (внешние ключи сами проверят существование)
    INSERT INTO containing (establishment_adress, product_article, containing_num)
    VALUES (add_establishment_adress, add_product_article, add_quantity);
    
    RAISE NOTICE 'Запись успешно добавлена';
    
EXCEPTION
    -- Если возникает ошибка внешнего ключа
    WHEN foreign_key_violation THEN
        RAISE NOTICE 'Ошибка: Проверьте существование заведения или товара';
END;
$$;


ALTER PROCEDURE public.add_stock(IN add_establishment_adress character varying, IN add_product_article smallint, IN add_quantity smallint) OWNER TO postgres;

--
-- TOC entry 231 (class 1255 OID 17634)
-- Name: set_delivered(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.set_delivered() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NEW.supply_state = 2 AND (OLD.supply_state IS NULL OR OLD.supply_state != 2) THEN
        NEW.supply_date_recieved := CURRENT_DATE;
    END IF;
    
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.set_delivered() OWNER TO postgres;

--
-- TOC entry 230 (class 1255 OID 17632)
-- Name: stock_update(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.stock_update() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    UPDATE containing c
    SET containing_num = c.containing_num - NEW.bill_content_count
    FROM bill b WHERE b.bill_id = NEW.bill_id
    AND c.establishment_adress = b.establishment_adress
    AND c.product_article = NEW.product_article;
    
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.stock_update() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 216 (class 1259 OID 17371)
-- Name: bill; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bill (
    bill_id integer NOT NULL,
    loyalty_card_number smallint,
    establishment_adress character varying(100) NOT NULL,
    bill_paytype smallint NOT NULL,
    bill_timedate timestamp with time zone NOT NULL
);


ALTER TABLE public.bill OWNER TO postgres;

--
-- TOC entry 3463 (class 0 OID 0)
-- Dependencies: 216
-- Name: COLUMN bill.bill_paytype; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.bill.bill_paytype IS '0 - наличными
1 - картой
2 - приложением';


--
-- TOC entry 217 (class 1259 OID 17379)
-- Name: bill_content; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bill_content (
    bill_id smallint NOT NULL,
    product_article smallint NOT NULL,
    bill_content_count smallint NOT NULL,
    bill_summ numeric(50,2),
    bill_content_id integer NOT NULL
);


ALTER TABLE public.bill_content OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 17584)
-- Name: bill_content_bill_content_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bill_content_bill_content_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bill_content_bill_content_id_seq OWNER TO postgres;

--
-- TOC entry 3464 (class 0 OID 0)
-- Dependencies: 228
-- Name: bill_content_bill_content_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bill_content_bill_content_id_seq OWNED BY public.bill_content.bill_content_id;


--
-- TOC entry 218 (class 1259 OID 17387)
-- Name: containing; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.containing (
    establishment_adress character varying(100) NOT NULL,
    product_article smallint,
    containing_num smallint NOT NULL,
    containing_id smallint NOT NULL
);


ALTER TABLE public.containing OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 17566)
-- Name: containing_containing_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.containing ALTER COLUMN containing_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.containing_containing_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 219 (class 1259 OID 17394)
-- Name: content_supply; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.content_supply (
    product_article smallint NOT NULL,
    supply_id smallint NOT NULL,
    content_supply_num smallint NOT NULL,
    content_supply_cost integer NOT NULL,
    content_supply_id integer NOT NULL
);


ALTER TABLE public.content_supply OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 17597)
-- Name: content_supply_content_supply_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.content_supply_content_supply_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.content_supply_content_supply_id_seq OWNER TO postgres;

--
-- TOC entry 3465 (class 0 OID 0)
-- Dependencies: 229
-- Name: content_supply_content_supply_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.content_supply_content_supply_id_seq OWNED BY public.content_supply.content_supply_id;


--
-- TOC entry 221 (class 1259 OID 17403)
-- Name: employee; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.employee (
    employee_contract integer NOT NULL,
    establishment_adress character varying(100),
    employee_fullname character varying(100) NOT NULL,
    employee_position character varying(100)
);


ALTER TABLE public.employee OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 17402)
-- Name: employee_employee_contract_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.employee_employee_contract_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.employee_employee_contract_seq OWNER TO postgres;

--
-- TOC entry 3466 (class 0 OID 0)
-- Dependencies: 220
-- Name: employee_employee_contract_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.employee_employee_contract_seq OWNED BY public.employee.employee_contract;


--
-- TOC entry 222 (class 1259 OID 17412)
-- Name: establishment; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.establishment (
    establishment_adress character varying(100) NOT NULL
);


ALTER TABLE public.establishment OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 17418)
-- Name: loyalty_card; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.loyalty_card (
    loyalty_card_number smallint NOT NULL,
    loyalty_card_owner_name character varying(100) NOT NULL,
    loyalty_card_owner_number character(10) NOT NULL
);


ALTER TABLE public.loyalty_card OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 17424)
-- Name: product; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.product (
    product_article smallint NOT NULL,
    product_selfcost numeric(50,2) NOT NULL,
    product_name character varying(70) NOT NULL,
    product_factcost numeric(50,2)
);


ALTER TABLE public.product OWNER TO postgres;

--
-- TOC entry 214 (class 1259 OID 17357)
-- Name: sending_supply; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sending_supply (
    supply_id smallint NOT NULL,
    supplier_id smallint NOT NULL,
    sending_supply_count integer NOT NULL
);


ALTER TABLE public.sending_supply OWNER TO postgres;

--
-- TOC entry 215 (class 1259 OID 17365)
-- Name: supplier; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.supplier (
    supplier_id smallint NOT NULL,
    supplier_name character varying(100) NOT NULL,
    supplier_com_method character varying(50)
);


ALTER TABLE public.supplier OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 17431)
-- Name: supply; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.supply (
    supply_id smallint NOT NULL,
    establishment_adress character varying(100),
    supply_date_send date NOT NULL,
    supply_date_recieved date,
    supply_state smallint NOT NULL
);


ALTER TABLE public.supply OWNER TO postgres;

--
-- TOC entry 3467 (class 0 OID 0)
-- Dependencies: 225
-- Name: COLUMN supply.supply_state; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.supply.supply_state IS '0 - неизвестно
1 - отправлена
2 - доставлена';


--
-- TOC entry 226 (class 1259 OID 17438)
-- Name: transportation; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.transportation (
    transportation_id smallint NOT NULL,
    product_article smallint,
    establishment_adress_from character varying(100),
    establishment_adress_to character varying(100),
    transportation_status smallint NOT NULL,
    transportation_type smallint NOT NULL,
    transportation_time time without time zone,
    transportation_distance numeric(5,3)
);


ALTER TABLE public.transportation OWNER TO postgres;

--
-- TOC entry 3468 (class 0 OID 0)
-- Dependencies: 226
-- Name: COLUMN transportation.transportation_status; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.transportation.transportation_status IS '0 - неизвестно
1 - в пути
2 - доставлено';


--
-- TOC entry 3469 (class 0 OID 0)
-- Dependencies: 226
-- Name: COLUMN transportation.transportation_type; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.transportation.transportation_type IS '0 - внешняя
1 - внутренняя';


--
-- TOC entry 3223 (class 2604 OID 17585)
-- Name: bill_content bill_content_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bill_content ALTER COLUMN bill_content_id SET DEFAULT nextval('public.bill_content_bill_content_id_seq'::regclass);


--
-- TOC entry 3224 (class 2604 OID 17598)
-- Name: content_supply content_supply_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.content_supply ALTER COLUMN content_supply_id SET DEFAULT nextval('public.content_supply_content_supply_id_seq'::regclass);


--
-- TOC entry 3225 (class 2604 OID 17406)
-- Name: employee employee_contract; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee ALTER COLUMN employee_contract SET DEFAULT nextval('public.employee_employee_contract_seq'::regclass);


--
-- TOC entry 3444 (class 0 OID 17371)
-- Dependencies: 216
-- Data for Name: bill; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.bill (bill_id, loyalty_card_number, establishment_adress, bill_paytype, bill_timedate) FROM stdin;
7	\N	Командирская, д.21	2	2026-03-26 00:00:00+03
1	\N	Космонавтов, д.65/2	2	2025-12-08 11:59:30+03
5	\N	Иванушко, д.54	2	2025-02-15 22:00:31+03
2	1	Пушкина, д.58	2	2025-12-07 09:15:00+03
3	2	Ленина, д.63	2	2025-11-08 10:39:00+03
4	2	Колотушкина, д.53	0	2023-09-13 21:05:33+03
6	3	Командирская, д.21	0	2024-04-18 00:00:00+03
8	1	Пушкина, д.58	0	2024-06-15 14:30:00+03
9	\N	Ленина, д.63	0	2024-05-20 16:45:00+03
10	2	Космонавтов, д.65/2	0	2024-07-10 11:20:00+03
11	3	Командирская, д.21	0	2024-04-25 19:15:00+03
12	\N	Иванушко, д.54	0	2024-03-30 13:10:00+03
13	1	Колотушкина, д.53	0	2024-02-14 17:55:00+03
14	\N	Пушкина, д.58	0	2024-01-08 10:05:00+03
15	2	Ленина, д.63	0	2023-12-24 12:40:00+03
16	\N	Космонавтов, д.65/2	0	2023-11-18 15:25:00+03
17	3	Командирская, д.21	0	2023-10-31 18:50:00+03
18	1	Иванушко, д.54	0	2023-09-22 14:15:00+03
19	\N	Колотушкина, д.53	0	2023-08-19 16:30:00+03
20	2	Пушкина, д.58	0	2023-07-11 11:45:00+03
21	\N	Ленина, д.63	0	2023-06-05 13:20:00+03
22	3	Космонавтов, д.65/2	0	2023-05-29 19:05:00+03
23	1	Командирская, д.21	0	2023-04-18 10:50:00+03
24	\N	Иванушко, д.54	0	2023-03-12 15:35:00+03
25	2	Колотушкина, д.53	0	2023-02-28 17:10:00+03
\.


--
-- TOC entry 3445 (class 0 OID 17379)
-- Dependencies: 217
-- Data for Name: bill_content; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.bill_content (bill_id, product_article, bill_content_count, bill_summ, bill_content_id) FROM stdin;
7	5	4	400.00	1
6	3	1	80.00	2
5	4	1	60.00	3
4	1	2	80.00	4
3	6	1	80.00	5
2	2	10	100.00	6
1	2	5	50.00	7
8	1	2	80.00	8
8	3	1	80.00	9
9	2	5	50.00	10
9	4	2	120.00	11
10	5	3	300.00	12
10	7	1	100.00	13
11	6	2	160.00	14
11	1	1	40.00	15
12	3	3	240.00	16
13	4	2	120.00	17
13	2	10	100.00	18
14	5	1	100.00	19
15	6	2	160.00	20
15	7	1	100.00	21
16	1	3	120.00	22
17	2	8	80.00	23
18	3	2	160.00	24
18	4	1	60.00	25
19	5	4	400.00	26
20	6	1	80.00	27
21	7	2	200.00	28
22	1	2	80.00	29
22	2	5	50.00	30
23	3	3	240.00	31
24	4	2	120.00	32
25	5	1	100.00	33
25	6	1	80.00	34
9	1	5	200.00	35
\.


--
-- TOC entry 3446 (class 0 OID 17387)
-- Dependencies: 218
-- Data for Name: containing; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.containing (establishment_adress, product_article, containing_num, containing_id) FROM stdin;
Командирская, д.21	1	20	3
Командирская, д.21	2	50	4
Командирская, д.21	3	15	5
Командирская, д.21	4	10	6
Командирская, д.21	5	30	7
Командирская, д.21	6	25	8
Командирская, д.21	7	40	9
Иванушко, д.54	2	40	11
Иванушко, д.54	3	20	12
Иванушко, д.54	4	15	13
Иванушко, д.54	6	30	15
Иванушко, д.54	7	45	16
Колотушкина, д.53	1	18	17
Колотушкина, д.53	2	45	18
Колотушкина, д.53	3	22	19
Колотушкина, д.53	4	12	20
Колотушкина, д.53	6	35	22
Колотушкина, д.53	7	38	23
Пушкина, д.58	1	22	24
Пушкина, д.58	2	35	25
Пушкина, д.58	3	18	26
Пушкина, д.58	4	20	27
Пушкина, д.58	5	40	28
Пушкина, д.58	6	28	29
Ленина, д.63	2	60	32
Ленина, д.63	3	25	33
Ленина, д.63	4	18	34
Ленина, д.63	5	32	35
Ленина, д.63	6	40	36
Ленина, д.63	7	50	37
Космонавтов, д.65/2	1	28	38
Космонавтов, д.65/2	2	55	39
Космонавтов, д.65/2	3	20	40
Космонавтов, д.65/2	4	22	41
Космонавтов, д.65/2	5	38	42
Космонавтов, д.65/2	6	32	43
Космонавтов, д.65/2	7	48	44
Ленина, д.63	1	25	31
Космонавтов, д.65/2	3	15	45
Колотушкина, д.53	5	28	21
Ленина, д.63	8	52	51
Пушкина, д.58	7	42	30
Иванушко, д.54	5	45	14
Иванушко, д.54	1	25	10
Иванушко, д.54	1	25	50
\.


--
-- TOC entry 3447 (class 0 OID 17394)
-- Dependencies: 219
-- Data for Name: content_supply; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.content_supply (product_article, supply_id, content_supply_num, content_supply_cost, content_supply_id) FROM stdin;
4	16	85	5100	57
5	17	95	950	58
6	18	105	7350	59
7	19	115	1150	60
1	20	125	2500	61
2	21	135	675	62
3	22	145	4350	63
4	23	155	9300	64
5	24	165	1650	65
6	25	175	12250	66
7	26	185	1850	67
1	27	195	3900	68
2	28	205	1025	69
3	29	215	6450	70
4	30	225	13500	71
1	1	50	1000	1
2	1	100	500	2
3	2	80	2400	3
4	3	60	3600	4
5	4	40	400	5
6	5	70	4900	6
7	6	90	900	7
1	7	55	1100	8
2	8	110	550	9
3	9	85	2550	10
4	10	65	3900	11
5	11	45	450	12
6	12	75	5250	13
7	13	95	950	14
1	14	60	1200	15
2	15	120	600	16
3	1	75	2250	17
4	2	55	3300	18
5	3	35	350	19
6	4	65	4550	20
7	5	85	850	21
1	6	70	1400	22
2	7	130	650	23
3	8	90	2700	24
4	9	70	4200	25
5	10	50	500	26
6	11	80	5600	27
7	12	100	1000	28
\.


--
-- TOC entry 3449 (class 0 OID 17403)
-- Dependencies: 221
-- Data for Name: employee; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.employee (employee_contract, establishment_adress, employee_fullname, employee_position) FROM stdin;
2	Командирская, д.21	Гуров Сергей Андреевич	Продавец-кассир
3	Иванушко, д.54	Иванов Михаил Иванович	Продавец-кассир
4	Иванушко, д.54	Буров Илья Михайлович	Директор
5	Колотушкина, д.53	Шнуров Богдан Витальевич	Продавец-кассир
6	Колотушкина, д.53	Андреев Андрей Андреевич	Продавец-кассир
9	Ленина, д.63	Умнов Алексей Артёмович	Продавец-кассир
10	Ленина, д.63	Паровозов Аркадий Сергеевич	Директор
11	Космонавтов, д.65/2	Гослинг Райан Сергеевич	Продавец-кассир
12	Космонавтов, д.65/2	Подкравшихся Антон Павлович	Пекарь
13	Космонавтов, д.65/2	Сдавшихся Кодер Сергеевич	Продавец-кассир
14	Командирская, д.21	Петров Пётр Петрович	Уборщик
15	Командирская, д.21	Сидоров Сидор Сидорович	Охранник
17	Иванушко, д.54	Николаев Николай Николаевич	Охранник
18	Колотушкина, д.53	Васильев Василий Васильевич	Уборщик
19	Колотушкина, д.53	Михайлов Михаил Михайлович	Директор по развитию
20	Пушкина, д.58	Алексеев Алексей Алексеевич	Кондитер
21	Пушкина, д.58	Дмитриев Дмитрий Дмитриевич	Помощник пекаря
22	Ленина, д.63	Сергеев Сергей Сергеевич	Кассир
24	Космонавтов, д.65/2	Павлов Павел Павлович	Заведующий складом
25	Космонавтов, д.65/2	Фёдоров Фёдор Фёдорович	Охранник
26	Командирская, д.21	Егоров Егор Егорович	Грузчик
27	Иванушко, д.54	Семёнов Семён Семёнович	Водитель
28	Колотушкина, д.53	Тимофеев Тимофей Тимофеевич	Экспедитор
8	Пушкина, д.58	Безумов Никита Иванович	Пекарь
\.


--
-- TOC entry 3450 (class 0 OID 17412)
-- Dependencies: 222
-- Data for Name: establishment; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.establishment (establishment_adress) FROM stdin;
Командирская, д.21
Иванушко, д.54
Колотушкина, д.53
Пушкина, д.58
Ленина, д.63
Космонавтов, д.65/2
\.


--
-- TOC entry 3451 (class 0 OID 17418)
-- Dependencies: 223
-- Data for Name: loyalty_card; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.loyalty_card (loyalty_card_number, loyalty_card_owner_name, loyalty_card_owner_number) FROM stdin;
3	Морковкин Богдан Евгеньевич	9155542109
2	Жданов Михаил Сергеевич	8005553535
1	Струков Андрей Неизвестнович 	9067895353
4	Артёмов Григорий Григорьевич	9046071969
5	Шнуров Борис Евгеньевич	9152191109
6	ИП Петров М.С.	8509502232
\.


--
-- TOC entry 3452 (class 0 OID 17424)
-- Dependencies: 224
-- Data for Name: product; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.product (product_article, product_selfcost, product_name, product_factcost) FROM stdin;
7	10.00	Чай Иван	150.00
6	30.00	Кефир Ладушки	120.00
1	20.00	Яблоко Бабушка Джонсон	60.00
2	0.00	Сахар Бесплатный	0.00
3	20.00	Молоко Бабушкины гуси	120.00
8	15.00	Шоколад Алёнка	120.00
5	10.00	Арахис Пустышка	150.00
4	40.00	Желе Бескостное	90.00
\.


--
-- TOC entry 3442 (class 0 OID 17357)
-- Dependencies: 214
-- Data for Name: sending_supply; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sending_supply (supply_id, supplier_id, sending_supply_count) FROM stdin;
1	1	100
2	2	150
3	3	200
4	4	120
5	1	180
6	2	220
7	3	130
8	4	140
9	1	160
10	2	170
11	3	190
12	4	210
13	1	230
14	2	240
15	3	250
16	4	160
17	1	170
18	2	180
19	3	190
20	4	200
21	1	210
22	2	220
23	3	230
24	4	240
25	1	250
26	2	260
27	3	270
28	4	280
29	1	290
30	2	300
\.


--
-- TOC entry 3443 (class 0 OID 17365)
-- Dependencies: 215
-- Data for Name: supplier; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.supplier (supplier_id, supplier_name, supplier_com_method) FROM stdin;
1	ООО "Сладкая жизнь"	9005003001
2	ИП Петров М.С.	8509502232
3	ЗАО "Молочный мир"	milkworld@world.ru
4	АО "Напитки Сибири"	colddrink@bk.ru
5	ООО "Горькая жизнь"	9045542109
\.


--
-- TOC entry 3453 (class 0 OID 17431)
-- Dependencies: 225
-- Data for Name: supply; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.supply (supply_id, establishment_adress, supply_date_send, supply_date_recieved, supply_state) FROM stdin;
1	Командирская, д.21	2024-01-10	2024-01-12	2
2	Иванушко, д.54	2024-01-15	2024-01-17	2
3	Колотушкина, д.53	2024-02-05	2024-02-07	2
4	Пушкина, д.58	2024-02-10	2024-02-12	2
5	Ленина, д.63	2024-03-01	2024-03-03	2
6	Космонавтов, д.65/2	2024-03-10	2024-03-12	2
7	Командирская, д.21	2024-04-05	2024-04-07	2
9	Колотушкина, д.53	2024-05-01	\N	1
10	Пушкина, д.58	2024-05-10	2024-05-12	2
11	Ленина, д.63	2023-12-01	2023-12-03	2
12	Космонавтов, д.65/2	2023-12-10	2023-12-12	2
13	Командирская, д.21	2023-11-05	2023-11-07	2
14	Иванушко, д.54	2023-11-10	2023-11-12	2
15	Колотушкина, д.53	2023-10-01	2023-10-03	2
16	Пушкина, д.58	2023-09-05	2023-09-07	2
17	Ленина, д.63	2023-09-15	2023-09-17	2
18	Космонавтов, д.65/2	2023-08-10	2023-08-12	2
19	Командирская, д.21	2023-08-20	2023-08-22	2
20	Иванушко, д.54	2023-07-01	2023-07-03	2
21	Колотушкина, д.53	2023-07-10	2023-07-12	2
22	Пушкина, д.58	2023-06-05	2023-06-07	2
23	Ленина, д.63	2023-06-15	\N	1
24	Космонавтов, д.65/2	2023-05-10	2023-05-12	2
25	Командирская, д.21	2023-05-20	2023-05-22	2
26	Иванушко, д.54	2023-04-01	2023-04-03	2
27	Колотушкина, д.53	2023-04-10	\N	1
28	Пушкина, д.58	2023-03-05	2023-03-07	2
29	Ленина, д.63	2023-03-15	2023-03-17	2
30	Космонавтов, д.65/2	2023-02-10	2023-02-12	0
8	Иванушко, д.54	2024-04-10	2025-12-24	2
\.


--
-- TOC entry 3454 (class 0 OID 17438)
-- Dependencies: 226
-- Data for Name: transportation; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.transportation (transportation_id, product_article, establishment_adress_from, establishment_adress_to, transportation_status, transportation_type, transportation_time, transportation_distance) FROM stdin;
1	1	Командирская, д.21	Иванушко, д.54	1	0	08:00:00	1.530
3	3	Колотушкина, д.53	Пушкина, д.58	1	0	04:15:00	12.750
4	4	Пушкина, д.58	Ленина, д.63	2	1	01:45:00	3.800
5	5	Ленина, д.63	Космонавтов, д.65/2	1	0	06:30:00	18.200
6	6	Космонавтов, д.65/2	Командирская, д.21	2	1	03:20:00	8.900
7	7	Командирская, д.21	Иванушко, д.54	1	0	05:10:00	14.600
8	1	Иванушко, д.54	Колотушкина, д.53	2	1	02:15:00	4.950
10	3	Пушкина, д.58	Ленина, д.63	2	1	01:30:00	3.250
11	4	Ленина, д.63	Космонавтов, д.65/2	2	1	01:20:00	2.800
12	5	Космонавтов, д.65/2	Командирская, д.21	1	0	03:45:00	10.500
13	6	Командирская, д.21	Иванушко, д.54	2	1	02:50:00	7.200
14	7	Иванушко, д.54	Колотушкина, д.53	1	0	04:30:00	12.100
15	1	Колотушкина, д.53	Пушкина, д.58	2	1	02:00:00	4.300
17	3	Ленина, д.63	Космонавтов, д.65/2	2	1	01:45:00	3.900
18	4	Космонавтов, д.65/2	Командирская, д.21	1	0	03:20:00	9.800
19	5	Командирская, д.21	Иванушко, д.54	2	1	02:15:00	5.600
20	6	Иванушко, д.54	Колотушкина, д.53	1	0	03:50:00	11.300
21	7	Колотушкина, д.53	Пушкина, д.58	2	1	01:55:00	4.700
22	1	Пушкина, д.58	Ленина, д.63	1	0	00:55:00	1.800
24	3	Космонавтов, д.65/2	Командирская, д.21	1	0	03:05:00	9.100
25	4	Командирская, д.21	Иванушко, д.54	2	1	02:40:00	6.900
26	5	Иванушко, д.54	Колотушкина, д.53	1	0	04:15:00	12.400
27	6	Колотушкина, д.53	Пушкина, д.58	2	1	02:10:00	5.200
28	7	Пушкина, д.58	Ленина, д.63	1	0	01:05:00	2.000
29	1	Ленина, д.63	Космонавтов, д.65/2	2	1	01:50:00	4.100
\.


--
-- TOC entry 3470 (class 0 OID 0)
-- Dependencies: 228
-- Name: bill_content_bill_content_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.bill_content_bill_content_id_seq', 35, true);


--
-- TOC entry 3471 (class 0 OID 0)
-- Dependencies: 227
-- Name: containing_containing_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.containing_containing_id_seq', 53, true);


--
-- TOC entry 3472 (class 0 OID 0)
-- Dependencies: 229
-- Name: content_supply_content_supply_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.content_supply_content_supply_id_seq', 71, true);


--
-- TOC entry 3473 (class 0 OID 0)
-- Dependencies: 220
-- Name: employee_employee_contract_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.employee_employee_contract_seq', 16, true);


--
-- TOC entry 3244 (class 2606 OID 17587)
-- Name: bill_content bill_content_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bill_content
    ADD CONSTRAINT bill_content_pkey PRIMARY KEY (bill_content_id);


--
-- TOC entry 3249 (class 2606 OID 17583)
-- Name: containing containing_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.containing
    ADD CONSTRAINT containing_pkey PRIMARY KEY (containing_id);


--
-- TOC entry 3254 (class 2606 OID 17600)
-- Name: content_supply content_supply_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.content_supply
    ADD CONSTRAINT content_supply_pkey PRIMARY KEY (content_supply_id);


--
-- TOC entry 3240 (class 2606 OID 17545)
-- Name: bill pk_bill; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bill
    ADD CONSTRAINT pk_bill PRIMARY KEY (bill_id);


--
-- TOC entry 3262 (class 2606 OID 17408)
-- Name: employee pk_employee; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee
    ADD CONSTRAINT pk_employee PRIMARY KEY (employee_contract);


--
-- TOC entry 3265 (class 2606 OID 17416)
-- Name: establishment pk_establishment; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.establishment
    ADD CONSTRAINT pk_establishment PRIMARY KEY (establishment_adress);


--
-- TOC entry 3268 (class 2606 OID 17422)
-- Name: loyalty_card pk_loyalty_card; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.loyalty_card
    ADD CONSTRAINT pk_loyalty_card PRIMARY KEY (loyalty_card_number);


--
-- TOC entry 3270 (class 2606 OID 17428)
-- Name: product pk_product; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product
    ADD CONSTRAINT pk_product PRIMARY KEY (product_article);


--
-- TOC entry 3234 (class 2606 OID 17369)
-- Name: supplier pk_supplier; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supplier
    ADD CONSTRAINT pk_supplier PRIMARY KEY (supplier_id);


--
-- TOC entry 3274 (class 2606 OID 17435)
-- Name: supply pk_supply; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supply
    ADD CONSTRAINT pk_supply PRIMARY KEY (supply_id);


--
-- TOC entry 3280 (class 2606 OID 17442)
-- Name: transportation pk_transportation; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.transportation
    ADD CONSTRAINT pk_transportation PRIMARY KEY (transportation_id);


--
-- TOC entry 3229 (class 2606 OID 17624)
-- Name: sending_supply sending_supply_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sending_supply
    ADD CONSTRAINT sending_supply_pkey PRIMARY KEY (supply_id);


--
-- TOC entry 3247 (class 2606 OID 17596)
-- Name: bill_content uk_bill_content; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bill_content
    ADD CONSTRAINT uk_bill_content UNIQUE (bill_id, product_article);


--
-- TOC entry 3257 (class 2606 OID 17609)
-- Name: content_supply uk_content_supply; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.content_supply
    ADD CONSTRAINT uk_content_supply UNIQUE (product_article, supply_id);


--
-- TOC entry 3232 (class 2606 OID 17626)
-- Name: sending_supply uk_sending_supply; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sending_supply
    ADD CONSTRAINT uk_sending_supply UNIQUE (supply_id, supplier_id);


--
-- TOC entry 3241 (class 1259 OID 17386)
-- Name: bill_bill_content_has_fk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX bill_bill_content_has_fk ON public.bill_content USING btree (bill_id);


--
-- TOC entry 3242 (class 1259 OID 17384)
-- Name: bill_content_pk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX bill_content_pk ON public.bill_content USING btree (bill_id, product_article);


--
-- TOC entry 3245 (class 1259 OID 17385)
-- Name: bill_content_product_has_fk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX bill_content_product_has_fk ON public.bill_content USING btree (product_article);


--
-- TOC entry 3236 (class 1259 OID 17377)
-- Name: bill_loyalty_card_takespart_fk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX bill_loyalty_card_takespart_fk ON public.bill USING btree (loyalty_card_number);


--
-- TOC entry 3237 (class 1259 OID 17546)
-- Name: bill_pk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX bill_pk ON public.bill USING btree (bill_id);


--
-- TOC entry 3251 (class 1259 OID 17401)
-- Name: cont_supp_prod_contains_fk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cont_supp_prod_contains_fk ON public.content_supply USING btree (product_article);


--
-- TOC entry 3252 (class 1259 OID 17399)
-- Name: content_supply_pk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX content_supply_pk ON public.content_supply USING btree (product_article, supply_id);


--
-- TOC entry 3258 (class 1259 OID 17411)
-- Name: employee_find; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employee_find ON public.employee USING btree (employee_fullname);


--
-- TOC entry 3259 (class 1259 OID 17409)
-- Name: employee_pk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX employee_pk ON public.employee USING btree (employee_contract);


--
-- TOC entry 3277 (class 1259 OID 17445)
-- Name: estab_transp_from_fk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX estab_transp_from_fk ON public.transportation USING btree (establishment_adress_from);


--
-- TOC entry 3278 (class 1259 OID 17446)
-- Name: estab_transp_to_fk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX estab_transp_to_fk ON public.transportation USING btree (establishment_adress_to);


--
-- TOC entry 3238 (class 1259 OID 17378)
-- Name: establishment_bill_prints_fk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX establishment_bill_prints_fk ON public.bill USING btree (establishment_adress);


--
-- TOC entry 3260 (class 1259 OID 17410)
-- Name: establishment_employee_works_fk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX establishment_employee_works_fk ON public.employee USING btree (establishment_adress);


--
-- TOC entry 3263 (class 1259 OID 17417)
-- Name: establishment_pk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX establishment_pk ON public.establishment USING btree (establishment_adress);


--
-- TOC entry 3266 (class 1259 OID 17423)
-- Name: loyalty_card_pk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX loyalty_card_pk ON public.loyalty_card USING btree (loyalty_card_number);


--
-- TOC entry 3250 (class 1259 OID 17393)
-- Name: product_containing_keeps_fk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX product_containing_keeps_fk ON public.containing USING btree (product_article);


--
-- TOC entry 3271 (class 1259 OID 17430)
-- Name: product_find; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX product_find ON public.product USING btree (product_name);


--
-- TOC entry 3272 (class 1259 OID 17429)
-- Name: product_pk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX product_pk ON public.product USING btree (product_article);


--
-- TOC entry 3226 (class 1259 OID 17364)
-- Name: send_supply_supply_contains_fk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX send_supply_supply_contains_fk ON public.sending_supply USING btree (supply_id);


--
-- TOC entry 3227 (class 1259 OID 17362)
-- Name: sending_supply_pk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX sending_supply_pk ON public.sending_supply USING btree (supply_id, supplier_id);


--
-- TOC entry 3235 (class 1259 OID 17370)
-- Name: supplier_pk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX supplier_pk ON public.supplier USING btree (supplier_id);


--
-- TOC entry 3230 (class 1259 OID 17363)
-- Name: supplier_supply_provides_fk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX supplier_supply_provides_fk ON public.sending_supply USING btree (supplier_id);


--
-- TOC entry 3255 (class 1259 OID 17400)
-- Name: supply_contains_supply_has_fk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX supply_contains_supply_has_fk ON public.content_supply USING btree (supply_id);


--
-- TOC entry 3275 (class 1259 OID 17437)
-- Name: supply_establishment_takes_fk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX supply_establishment_takes_fk ON public.supply USING btree (establishment_adress);


--
-- TOC entry 3276 (class 1259 OID 17436)
-- Name: supply_pk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX supply_pk ON public.supply USING btree (supply_id);


--
-- TOC entry 3281 (class 1259 OID 17444)
-- Name: trans_prod_cont_in_fk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX trans_prod_cont_in_fk ON public.transportation USING btree (product_article);


--
-- TOC entry 3282 (class 1259 OID 17443)
-- Name: transportation_pk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX transportation_pk ON public.transportation USING btree (transportation_id);


--
-- TOC entry 3298 (class 2620 OID 17633)
-- Name: bill_content bought_update; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER bought_update AFTER INSERT ON public.bill_content FOR EACH ROW EXECUTE FUNCTION public.stock_update();


--
-- TOC entry 3299 (class 2620 OID 17635)
-- Name: supply update_delivery; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_delivery BEFORE UPDATE ON public.supply FOR EACH ROW EXECUTE FUNCTION public.set_delivered();


--
-- TOC entry 3285 (class 2606 OID 17457)
-- Name: bill fk_bill_bill_loya_loyalty_; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bill
    ADD CONSTRAINT fk_bill_bill_loya_loyalty_ FOREIGN KEY (loyalty_card_number) REFERENCES public.loyalty_card(loyalty_card_number) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3287 (class 2606 OID 17547)
-- Name: bill_content fk_bill_con_bill_bill_bill; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bill_content
    ADD CONSTRAINT fk_bill_con_bill_bill_bill FOREIGN KEY (bill_id) REFERENCES public.bill(bill_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3288 (class 2606 OID 17472)
-- Name: bill_content fk_bill_con_bill_cont_product; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bill_content
    ADD CONSTRAINT fk_bill_con_bill_cont_product FOREIGN KEY (product_article) REFERENCES public.product(product_article) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3286 (class 2606 OID 17462)
-- Name: bill fk_bill_establish_establis; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bill
    ADD CONSTRAINT fk_bill_establish_establis FOREIGN KEY (establishment_adress) REFERENCES public.establishment(establishment_adress) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3289 (class 2606 OID 17477)
-- Name: containing fk_containi_establish_establis; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.containing
    ADD CONSTRAINT fk_containi_establish_establis FOREIGN KEY (establishment_adress) REFERENCES public.establishment(establishment_adress) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3290 (class 2606 OID 17482)
-- Name: containing fk_containi_product_c_product; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.containing
    ADD CONSTRAINT fk_containi_product_c_product FOREIGN KEY (product_article) REFERENCES public.product(product_article) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3291 (class 2606 OID 17487)
-- Name: content_supply fk_content__content_s_product; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.content_supply
    ADD CONSTRAINT fk_content__content_s_product FOREIGN KEY (product_article) REFERENCES public.product(product_article) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3292 (class 2606 OID 17627)
-- Name: content_supply fk_content__supply_co_supply; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.content_supply
    ADD CONSTRAINT fk_content__supply_co_supply FOREIGN KEY (supply_id) REFERENCES public.sending_supply(supply_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3293 (class 2606 OID 17497)
-- Name: employee fk_employee_establish_establis; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee
    ADD CONSTRAINT fk_employee_establish_establis FOREIGN KEY (establishment_adress) REFERENCES public.establishment(establishment_adress) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3283 (class 2606 OID 17447)
-- Name: sending_supply fk_sending__send_supp_supply; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sending_supply
    ADD CONSTRAINT fk_sending__send_supp_supply FOREIGN KEY (supply_id) REFERENCES public.supply(supply_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3284 (class 2606 OID 17452)
-- Name: sending_supply fk_sending__supplier__supplier; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sending_supply
    ADD CONSTRAINT fk_sending__supplier__supplier FOREIGN KEY (supplier_id) REFERENCES public.supplier(supplier_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3294 (class 2606 OID 17502)
-- Name: supply fk_supply_supply_es_establis; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supply
    ADD CONSTRAINT fk_supply_supply_es_establis FOREIGN KEY (establishment_adress) REFERENCES public.establishment(establishment_adress) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3295 (class 2606 OID 17517)
-- Name: transportation fk_transpor_transport_product; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.transportation
    ADD CONSTRAINT fk_transpor_transport_product FOREIGN KEY (product_article) REFERENCES public.product(product_article) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3296 (class 2606 OID 17507)
-- Name: transportation fk_transport_from; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.transportation
    ADD CONSTRAINT fk_transport_from FOREIGN KEY (establishment_adress_from) REFERENCES public.establishment(establishment_adress) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3297 (class 2606 OID 17512)
-- Name: transportation fk_transport_to; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.transportation
    ADD CONSTRAINT fk_transport_to FOREIGN KEY (establishment_adress_to) REFERENCES public.establishment(establishment_adress) ON UPDATE CASCADE ON DELETE RESTRICT;


-- Completed on 2026-04-28 14:02:53

--
-- PostgreSQL database dump complete
--

\unrestrict IUP1aDYd4EAlF3Fm1E6lvHJsoKeRC9kNVERLKQYXAKGJXX5RHpB0qKj50a2kMf6

