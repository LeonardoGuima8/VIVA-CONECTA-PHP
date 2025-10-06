-- Enable RLS
alter table users enable row level security;
alter table profiles enable row level security;
alter table professionals enable row level security;
alter table clinics enable row level security;
alter table appointments enable row level security;
alter table reviews enable row level security;
alter table chat_threads enable row level security;
alter table chat_messages enable row level security;
alter table notifications enable row level security;
alter table coupons enable row level security;
alter table coupon_usages enable row level security;

-- Basic policies (adjust depending on Supabase auth.uid())
-- NOTE: Replace current_setting('request.jwt.claims',true) ->> 'sub' with auth.uid() in Supabase SQL templates.

create policy "users_self_select" on users
for select using (id::text = current_setting('request.jwt.claims', true) ->> 'sub');

create policy "profiles_self_manage" on profiles
for select using (user_id::text = current_setting('request.jwt.claims', true) ->> 'sub')
for update using (user_id::text = current_setting('request.jwt.claims', true) ->> 'sub')
for insert with check (user_id::text = current_setting('request.jwt.claims', true) ->> 'sub');

create policy "professionals_self_manage" on professionals
for select using (user_id::text = current_setting('request.jwt.claims', true) ->> 'sub')
for update using (user_id::text = current_setting('request.jwt.claims', true) ->> 'sub')
for insert with check (user_id::text = current_setting('request.jwt.claims', true) ->> 'sub');

create policy "clinics_self_manage" on clinics
for select using (user_id::text = current_setting('request.jwt.claims', true) ->> 'sub')
for update using (user_id::text = current_setting('request.jwt.claims', true) ->> 'sub')
for insert with check (user_id::text = current_setting('request.jwt.claims', true) ->> 'sub');

-- Appointments: patient or professional can see rows related to them
create policy "appointments_related_access" on appointments
for select using (
  patient_id::text = current_setting('request.jwt.claims', true) ->> 'sub'
  or professional_id::text = current_setting('request.jwt.claims', true) ->> 'sub'
);

-- Reviews visible to involved parties
create policy "reviews_view" on reviews
for select using (
  rater_id::text = current_setting('request.jwt.claims', true) ->> 'sub'
);

-- Public readable tables (no PII)
alter table specialties enable row level security;
alter table subspecialties enable row level security;
alter table plans enable row level security;
alter table tiers enable row level security;
alter table ads_slots enable row level security;

create policy "read_public_specialties" on specialties for select using (true);
create policy "read_public_subspecialties" on subspecialties for select using (true);
create policy "read_public_plans" on plans for select using (true);
create policy "read_public_tiers" on tiers for select using (true);
create policy "read_public_ads" on ads_slots for select using (true);
alter table availability_slots enable row level security;
create policy "availability_public_read" on availability_slots for select using (true);

alter table gamification_events enable row level security;
create policy "gamification_events_owner" on gamification_events
for select using (user_id::text = current_setting('request.jwt.claims', true) ->> 'sub')
for insert with check (user_id::text = current_setting('request.jwt.claims', true) ->> 'sub');

alter table gamification_scores enable row level security;
create policy "gamification_scores_owner" on gamification_scores
for select using (user_id::text = current_setting('request.jwt.claims', true) ->> 'sub');
