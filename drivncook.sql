create table if not exists admin
(
    id         serial not null
        constraint admin_pkey
            primary key,
    first_name varchar(255),
    last_name  varchar(255),
    email      varchar(255) UNIQUE,
    password   varchar(70),
    created_at timestamp
);
create table if not exists truck
(
    id            serial not null
        constraint truck_pkey
            primary key,
    matriculation varchar(255) UNIQUE ,
    longitude      double precision,
    latitude      double precision,
    active integer DEFAULT 1,
    created_at    timestamp
);
create table if not exists warehouse
(
    id         serial not null
        constraint warehouse_pkey
            primary key,
    location   varchar(255),
    name       varchar(255),
    created_at timestamp
);
create table if not exists warehouse_category
(
    id         serial not null
        constraint warehouse_category_pkey
            primary key,
    name       varchar(255),
    parent_id  integer
        constraint warehouse_category_parent_id_fkey
            references warehouse_category,
    created_at timestamp
);
create table if not exists warehouse_item
(
    id         serial not null
        constraint warehouse_item_pkey
            primary key,
    name       varchar(255),
    price      double precision,
    resale_price double precision,
    tva        double precision,
    category   integer
        constraint warehouse_item_category_fkey
            references warehouse_category,
    created_at timestamp
);
create table if not exists warehouse_stock_item
(
    id         serial not null
        constraint warehouse_stock_item_pkey
            primary key,
    item      integer
        constraint warehouse_stock_item_item_fkey
            references warehouse_item,
    warehouse integer
        constraint warehouse_stock_item_warehouse_fkey
            references warehouse,
    quantity  integer
);
create table if not exists users
(
    id         serial not null
        constraint user_pkey
            primary key,
    truck_id   integer
        constraint user_truck_id_fkey
            references truck,
    first_name varchar(255),
    last_name  varchar(255),
    society_name varchar(255),
    siret varchar(80),
    address_line varchar(255),
    country varchar(100),
    postal_code varchar(80),
    address_city varchar(100),
    address_state varchar(100),
    phone_number varchar(80),
    email      varchar(255),
    password   varchar(70),
    stripe_public_key varchar(255),
    stripe_private_key varchar(255),
    is_active boolean default true,
    created_at timestamp
);
create table if not exists customer
(
    id         serial not null
        constraint customer_pkey
            primary key,
    first_name varchar(255),
    last_name  varchar(255),
    email      varchar(255),
    password   varchar(70),
    created_at timestamp
);
create table if not exists invoice
(
    id          serial not null
        constraint invoice_pkey
            primary key,
    recipient   varchar(255),
    owner      varchar(255),
    owner_address_line varchar(255),
    owner_postal_code varchar(255),
    owner_city varchar(255),
    user_id     integer
        constraint invoice_user_id_fkey
            references users,
    customer_id integer
        constraint invoice_customer_id_fkey
            references customer,
    warehouse_id integer
        constraint invoice_warehouse_id_fkey
            references warehouse,
    status      boolean                  default false,
    created_at  timestamp
);
create table if not exists invoice_line
(
        id          serial not null
        constraint invoice_line_pkey
            primary key,
    invoice_id integer
        constraint invoice_line_invoice_id_fkey
            references invoice,
    text       varchar(255),
    quantity   integer,
    price      double precision,
    tva        double precision
);
create table if not exists maintenance
(
    id         serial                                                         not null
        constraint maintenance_pkey
            primary key,
    truck_id   integer
        constraint maintenance_truck_id_fkey
            references truck,
    status     varchar(255) not null,
    invoice    integer
        constraint maintenance_invoice_fkey
            references invoice,
    created_at timestamp
);
create table if not exists maintenance_info
(
    id             serial not null
        constraint maintenance_info_pkey
            primary key,
    maintenance_id integer
        constraint maintenance_info_maintenance_id_fkey
            references maintenance,
    info           text,
    created_at     timestamp
);
create table if not exists card
(
    id      serial not null
        constraint card_pkey
            primary key,
    name    varchar(255),
    user_id integer
        constraint card_user_id_fkey
            references users
);
create table if not exists card_category
(
    id         serial not null
        constraint card_category_pkey
            primary key,
    name       varchar(255),
    card       integer
        constraint card_category_card_fkey
            references card,
    created_at timestamp
);
create table if not exists card_item
(
    id          serial not null
        constraint card_item_pkey
            primary key,
    category_id integer
        constraint card_item_category_id_fkey
            references card_category,
    name        varchar(255),
    price       double precision,
    created_at  timestamp
);
create table if not exists event
(
    id                serial not null
        constraint event_pkey
            primary key,
    name              varchar(255),
    start_at          timestamp,
    end_at            timestamp,
    begin_register_at timestamp,
    description       text,
    created_at        timestamp
);
create table if not exists event_user
(
    id                serial not null
        constraint event_user_pkey
            primary key,
    event_id integer not null
        constraint event_user_event_id_fkey
            references event,
    user_id  integer not null
        constraint event_user_user_id_fkey
            references users
);
create table if not exists promotion
(
    id         serial not null
        constraint promotion_pkey
            primary key,
    user_id    integer
        constraint promotion_user_id_fkey
            references users,
    name       varchar(255),
    start_at   timestamp,
    end_at     timestamp,
    min_price double precision,
    max_price double precision,
    reduc_percentage double precision,
    max_commands integer,
    is_archived integer,
    created_at timestamp
);


create table if not exists fidelity_card
(
    customer_id integer not null
        constraint fidelity_card_pkey
            primary key
        constraint fidelity_card_customer_id_fkey
            references customer,
    nb_point    integer,
    end_at      timestamp,
    created_at  timestamp
);
create table if not exists orders
(
    id          serial not null
        constraint orders_pkey
            primary key,
    customer_id integer
        constraint orders_customer_id_fkey
            references customer,
    user_id     integer
        constraint orders_user_id_fkey
            references users,
    status integer default 1,
    recuperation_date integer not null,
    description varchar(255) default null,
    promotion_id integer
        constraint orders_promotion_id_fkey
            references promotion,
    created_at  timestamp
);
create table if not exists order_line
(
    id integer primary key,
    order_id integer
        constraint order_line_order_id_fkey
            references orders,
    text     varchar(255),
    quantity integer,
    price    double precision
);
create table if not exists event_customer
(
    id             serial not null
        constraint event_customer_pkey
            primary key,
    id_event integer constraint event_event_customer_id_fkey
            references event,
    id_customer integer constraint customer_event_customer_id_fkey
            references customer,
    statut varchar(255),
    code      varchar(255),
    created_at    timestamp
);

create table if not exists contact
(
  id serial not null
    constraint contact_pkey
        primary key,
    name varchar(255),
    phone varchar(50),
    email varchar(75),
    message varchar(255),
    is_read integer default 0,
    created_at timestamp
);

create table if not exists newletters
(
  id serial not null
    constraint newletters_pkey
        primary key,
    email varchar(255),
    created_at timestamp
);


ALTER TABLE event
    ADD COLUMN title_Email_FR varchar(255) NULL,
    ADD COLUMN text_Email_FR TEXT NULL,
    ADD COLUMN text_FR TEXT NULL,
    ADD COLUMN title_Email_EN varchar(255) NULL,
    ADD COLUMN text_Email_EN TEXT NULL,
    ADD COLUMN text_EN TEXT NULL,
    ADD COLUMN image varchar(255) NULL,
    ADD COLUMN "type" varchar(255) NULL;
-- Auto seed
INSERT INTO admin
        VALUES (1,'Maxime', 'LE HENAFF', 'maxime@lehenaff.pro', '$2y$10$DjAyq7IHAONyaqbc.VaaGea6G2WbMV4AACvU9HE07PsU.8CWy4xiC', '2020-04-18 10:10:10.0000000');
INSERT INTO admin
        VALUES (2,'Swann', 'HERRERA', 'swann@herrera.pro', '$2y$10$DjAyq7IHAONyaqbc.VaaGea6G2WbMV4AACvU9HE07PsU.8CWy4xiC', '2020-04-18 10:10:10.0000000');
INSERT INTO truck
        VALUES(1, 'AA-000-AA', 0.00, 0.00, 1, '2020-04-18 10:10:10.0000000');
INSERT INTO users
        VALUES (1, 1, 'Maxime', 'LE HENAFF', 'Massimo Trucks', '12345678', '242 rue du faubourg', 'France', '75012', 'Paris', 'Ile-de-France', '01111222', 'maxime@lehenaff.pro',
                '$2y$10$DjAyq7IHAONyaqbc.VaaGea6G2WbMV4AACvU9HE07PsU.8CWy4xiC', '', '', true, '2020-04-18 10:10:10.0000000');