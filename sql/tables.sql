create table sites(
    site_id bigserial not null primary key,
    url varchar(255),
    title varchar(255),
    short_desc text,
    indexdate date,
    spider_depth int default 2,
    required text,
    disallowed text,
    usesitemap int default 0,
    obey_robots int default 1,
    can_leave_domain int default 0,
    foreign_images int default 0);
create table links (
    link_id bigserial primary key not null,
    site_id bigint,
    url varchar(255) not null,
    title varchar(200),
    description varchar(255),
    fulltxt text,
    indexdate date,
    size float(2),
    md5sum varchar(32),
    visible int default 0,
    level int);
create table keywords    (
    keyword_id bigserial primary key not null,
    keyword varchar(35) not null unique);
create table link_keyword (
    link_id bigint not null,
    keyword_id bigint not null,
    weight int default null,
    domain int default null,
    primary key (link_id, keyword_id));
create table categories(
    category_id bigserial not null primary key,
    category text,
    parent_num integer);
create table site_category (
    site_id bigint,
    category_id integer);
create table temp (
    link varchar(255),
    level integer,
    id varchar (32));
create table pending (
    site_id bigint,
    temp_id varchar(32),
    level integer,
    count integer,
    num integer);
create table query_log (
    query varchar(255),
    time timestamp,
    elapsed float(2),
    results int);
create table domains (
    domain_id bigserial primary key not null,
    domain varchar(255));
