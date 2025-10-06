-- PostgreSQL / Supabase schema (core)
create extension if not exists "uuid-ossp";

create table if not exists users (
  id uuid primary key default uuid_generate_v4(),
  email text unique not null,
  name text not null,
  phone text,
  role text check (role in ('patient','professional','clinic','admin')) not null default 'patient',
  status text default 'active',
  created_at timestamptz default now()
);

create table if not exists profiles (
  user_id uuid primary key references users(id) on delete cascade,
  avatar_url text,
  bio text,
  address text,
  city text,
  state text,
  zipcode text,
  birthdate date,
  gender text,
  preferences jsonb default '{}'::jsonb
);

create table if not exists specialties (
  id uuid primary key default uuid_generate_v4(),
  name text not null,
  slug text unique not null,
  vertical text check (vertical in ('saude','beleza','especialistas')) not null
);

create table if not exists subspecialties (
  id uuid primary key default uuid_generate_v4(),
  specialty_id uuid references specialties(id) on delete cascade,
  name text not null,
  slug text unique not null
);

create table if not exists professionals (
  user_id uuid primary key references users(id) on delete cascade,
  council_type text,
  council_id text,
  years_experience int default 0,
  certifications jsonb default '[]'::jsonb,
  verified_at timestamptz,
  tier text check (tier in ('bronze','prata','ouro','platina')) default 'bronze'
);

create table if not exists professional_specialties (
  professional_id uuid references professionals(user_id) on delete cascade,
  specialty_id uuid references specialties(id) on delete cascade,
  subspecialty_id uuid references subspecialties(id) on delete cascade,
  primary key (professional_id, specialty_id, subspecialty_id)
);

create table if not exists clinics (
  user_id uuid primary key references users(id) on delete cascade,
  trade_name text,
  legal_name text,
  cnpj text,
  verification_status text default 'pending',
  photos text[]
);

create table if not exists plans (
  id uuid primary key default uuid_generate_v4(),
  health_insurance_name text not null,
  slug text unique not null,
  active boolean default true
);

create table if not exists professional_plans (
  professional_id uuid references professionals(user_id) on delete cascade,
  plan_id uuid references plans(id) on delete cascade,
  primary key (professional_id, plan_id)
);

create table if not exists locations (
  id uuid primary key default uuid_generate_v4(),
  label text,
  lat double precision,
  lng double precision,
  address text,
  city text,
  state text,
  zipcode text,
  region_df text
);

create table if not exists schedules (
  id uuid primary key default uuid_generate_v4(),
  professional_id uuid references professionals(user_id) on delete cascade,
  weekday int check (weekday between 0 and 6),
  start_time time not null,
  end_time time not null,
  telemedicine boolean default false
);

create table if not exists availability_overrides (
  id uuid primary key default uuid_generate_v4(),
  professional_id uuid references professionals(user_id) on delete cascade,
  date date not null,
  slots jsonb not null,
  status text default 'open'
);

create table if not exists appointments (
  id uuid primary key default uuid_generate_v4(),
  patient_id uuid references users(id) on delete set null,
  professional_id uuid references professionals(user_id) on delete set null,
  clinic_id uuid references clinics(user_id) on delete set null,
  type text check (type in ('presencial','tele')) default 'presencial',
  scheduled_at timestamptz not null,
  channel text check (channel in ('whatsapp','chat')) not null,
  status text check (status in ('pending','confirmed','completed','cancelled','no_show')) default 'pending',
  notes text,
  created_at timestamptz default now()
);

create table if not exists appointment_events (
  id uuid primary key default uuid_generate_v4(),
  appointment_id uuid references appointments(id) on delete cascade,
  event_type text not null,
  payload jsonb default '{}'::jsonb,
  created_at timestamptz default now()
);

create table if not exists chat_threads (
  id uuid primary key default uuid_generate_v4(),
  patient_id uuid references users(id) on delete set null,
  professional_id uuid references professionals(user_id) on delete set null,
  appointment_id uuid references appointments(id) on delete set null,
  status text default 'open'
);

create table if not exists chat_messages (
  id uuid primary key default uuid_generate_v4(),
  thread_id uuid references chat_threads(id) on delete cascade,
  sender_id uuid references users(id) on delete set null,
  content text,
  attachments text[],
  created_at timestamptz default now()
);

create table if not exists notifications (
  id uuid primary key default uuid_generate_v4(),
  user_id uuid references users(id) on delete cascade,
  type text,
  payload jsonb,
  status text default 'queued',
  scheduled_for timestamptz,
  sent_at timestamptz
);

create table if not exists reviews (
  id uuid primary key default uuid_generate_v4(),
  appointment_id uuid references appointments(id) on delete cascade,
  rater_id uuid references users(id) on delete set null,
  rating numeric(2,1) check (rating between 1 and 5),
  dimensions jsonb,
  comment text,
  published_at timestamptz
);

create table if not exists review_commitments (
  appointment_id uuid primary key references appointments(id) on delete cascade,
  shown_at timestamptz,
  accepted boolean
);

create table if not exists checkins (
  appointment_id uuid primary key references appointments(id) on delete cascade,
  method text check (method in ('qr','geo','manual')),
  verified_by uuid references users(id),
  verified_at timestamptz
);

create table if not exists tiers (
  id uuid primary key default uuid_generate_v4(),
  name text unique,
  benefits jsonb,
  price_monthly numeric(10,2)
);

create table if not exists subscriptions (
  id uuid primary key default uuid_generate_v4(),
  professional_id uuid references professionals(user_id) on delete cascade,
  clinic_id uuid references clinics(user_id) on delete cascade,
  tier_id uuid references tiers(id),
  status text,
  started_at timestamptz,
  renewed_at timestamptz,
  payment_provider text,
  invoice_ref text
);

create table if not exists coupons (
  id uuid primary key default uuid_generate_v4(),
  clinic_id uuid references clinics(user_id) on delete cascade,
  code text unique not null,
  title text,
  description text,
  category text,
  discount_type text check (discount_type in ('percent','fixed')),
  discount_value numeric(10,2),
  rules jsonb,
  valid_from timestamptz,
  valid_to timestamptz,
  active boolean default true
);

create table if not exists coupon_usages (
  id uuid primary key default uuid_generate_v4(),
  coupon_id uuid references coupons(id) on delete cascade,
  user_id uuid references users(id) on delete cascade,
  used_at timestamptz default now(),
  validation_ref text
);

create table if not exists ads_slots (
  id uuid primary key default uuid_generate_v4(),
  slot text,
  page text,
  targeting jsonb,
  active boolean default true
);

create table if not exists ads_impressions (
  id uuid primary key default uuid_generate_v4(),
  slot_id uuid references ads_slots(id) on delete cascade,
  user_id uuid,
  created_at timestamptz default now()
);

create table if not exists ads_clicks (
  id uuid primary key default uuid_generate_v4(),
  slot_id uuid references ads_slots(id) on delete cascade,
  user_id uuid,
  created_at timestamptz default now()
);

create table if not exists favorites (
  user_id uuid references users(id) on delete cascade,
  professional_id uuid references professionals(user_id) on delete cascade,
  created_at timestamptz default now(),
  primary key (user_id, professional_id)
);

create table if not exists families (
  owner_user_id uuid references users(id) on delete cascade,
  member_user_id uuid references users(id) on delete cascade,
  relation text,
  primary key (owner_user_id, member_user_id)
);

create table if not exists audit_logs (
  id uuid primary key default uuid_generate_v4(),
  actor_id uuid,
  action text,
  entity text,
  entity_id uuid,
  diff jsonb,
  created_at timestamptz default now()
);

create table if not exists search_history (
  id uuid primary key default uuid_generate_v4(),
  user_id uuid references users(id) on delete cascade,
  query text,
  filters jsonb,
  created_at timestamptz default now()
);

create table if not exists devices (
  id uuid primary key default uuid_generate_v4(),
  user_id uuid references users(id) on delete cascade,
  token text,
  created_at timestamptz default now()
);
-- Availability slots table populated via edge function availability_sync
create table if not exists availability_slots (
  id uuid primary key default uuid_generate_v4(),
  professional_id uuid references professionals(user_id) on delete cascade,
  date date not null,
  start_time time not null,
  end_time time not null,
  channel text default 'presencial',
  source text default 'schedule',
  created_at timestamptz default now()
);

-- Gamification core entities
create table if not exists gamification_events (
  id uuid primary key default uuid_generate_v4(),
  user_id uuid references users(id) on delete cascade,
  type text not null,
  points int default 0,
  metadata jsonb default '{}'::jsonb,
  created_at timestamptz default now()
);

create table if not exists gamification_scores (
  user_id uuid primary key references users(id) on delete cascade,
  total_points int default 0,
  level int default 1,
  badges jsonb default '[]'::jsonb,
  updated_at timestamptz default now()
);

create or replace function trg_update_gamification_scores()
returns trigger as $$
begin
  insert into gamification_scores(user_id, total_points, level, badges, updated_at)
  values (new.user_id, new.points, 1, '[]'::jsonb, now())
  on conflict (user_id) do update set
    total_points = gamification_scores.total_points + new.points,
    level = greatest(1, ((gamification_scores.total_points + new.points) / 500) + 1),
    updated_at = now();
  return new;
end;
$$ language plpgsql;

drop trigger if exists trg_gamification_events on gamification_events;
create trigger trg_gamification_events
  after insert on gamification_events
  for each row execute function trg_update_gamification_scores();

create or replace function gamification_summary(p_user_id uuid)
returns jsonb as $$
  select coalesce((
    select jsonb_build_object(
      'user_id', p_user_id,
      'total_points', coalesce(gs.total_points, 0),
      'level', coalesce(gs.level, 1),
      'badges', coalesce(gs.badges, '[]'::jsonb),
      'recent_events', coalesce((
        select jsonb_agg(row_to_json(e))
        from (
          select type, points, metadata, created_at
          from gamification_events
          where user_id = p_user_id
          order by created_at desc
          limit 10
        ) e
      ), '[]'::jsonb)
    )
    from gamification_scores gs
    where gs.user_id = p_user_id
  ), jsonb_build_object(
    'user_id', p_user_id,
    'total_points', 0,
    'level', 1,
    'badges', '[]'::jsonb,
    'recent_events', '[]'::jsonb
  ));
 language sql stable;

create or replace function dashboard_professional_summary(p_user_id uuid)
returns jsonb as $$
  select jsonb_build_object(
    'next_appointments', coalesce((
      select jsonb_agg(row_to_json(ap))
      from (
        select id, scheduled_at, patient_id, status
        from appointments
        where professional_id = p_user_id and scheduled_at > now()
        order by scheduled_at
        limit 5
      ) ap
    ), '[]'::jsonb),
    'ratings', coalesce((
      select jsonb_build_object(
        'average', avg(r.rating),
        'count', count(*))
      from reviews r
      join appointments a on a.id = r.appointment_id
      where a.professional_id = p_user_id
    ), jsonb_build_object('average', 0, 'count', 0))
  );
$$ language sql stable;

create or replace function dashboard_admin_summary()
returns jsonb as $$
  select jsonb_build_object(
    'users', (select count(*) from users),
    'professionals', (select count(*) from professionals),
    'clinics', (select count(*) from clinics),
    'appointments_last_30d', (select count(*) from appointments where scheduled_at >= now() - interval '30 days')
  );
$$ language sql stable;




