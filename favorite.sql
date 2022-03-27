CREATE TABLE favorites (
  id         int       primary key auto_increment,
  member_id  int       not null,
  post_id    int       not null,
  created_at timestamp not null,
  updated_at timestamp not null default CURRENT_TIMESTAMP
);
