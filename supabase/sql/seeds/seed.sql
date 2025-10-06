-- Basic seeds
insert into tiers (name, benefits, price_monthly) values
('bronze','{"visibility":"standard"}', 99.00),
('prata','{"visibility":"boosted"}', 159.00),
('ouro','{"visibility":"premium"}', 259.00)
on conflict do nothing;

insert into plans (health_insurance_name, slug) values
('Unimed','unimed'),
('Bradesco','bradesco'),
('SulAmérica','sulamerica'),
('Amil','amil'),
('Particular','particular')
on conflict do nothing;

-- Example specialties
insert into specialties (name, slug, vertical) values
('Cardiologia','cardiologia','saude'),
('Dermatologia','dermatologia','saude'),
('Psicologia','psicologia','especialistas'),
('Estética Facial','estetica-facial','beleza')
on conflict do nothing;
