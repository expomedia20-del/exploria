-- EXPLORIA Technical Design Pack v1.0 - PostgreSQL DDL Draft
-- Scope: Sprint 1 core path: QR Scan -> PWA -> OTP -> Consent -> Attributed Scan Event
-- Notes: This is an implementation baseline and may be refined by migration tooling.

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

CREATE TYPE record_status AS ENUM ('draft','active','inactive','archived','placeholder');
CREATE TYPE otp_status AS ENUM ('pending','verified','expired','blocked');
CREATE TYPE scan_result AS ENUM ('accepted','invalid','expired','inactive','duplicate','rate_limited','offline_queued','error');
CREATE TYPE sync_status AS ENUM ('queued','synced','failed','discarded');
CREATE TYPE issue_status AS ENUM ('open','in_progress','resolved','closed');

CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    mobile VARCHAR(32) UNIQUE,
    mobile_hash VARCHAR(128),
    status record_status NOT NULL DEFAULT 'active',
    preferred_language VARCHAR(8) DEFAULT 'fa',
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE otp_requests (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    mobile VARCHAR(32) NOT NULL,
    mobile_hash VARCHAR(128),
    code_hash VARCHAR(255) NOT NULL,
    channel VARCHAR(32) NOT NULL DEFAULT 'sms',
    attempts SMALLINT NOT NULL DEFAULT 0,
    max_attempts SMALLINT NOT NULL DEFAULT 5,
    expires_at TIMESTAMPTZ NOT NULL,
    status otp_status NOT NULL DEFAULT 'pending',
    source_qr_code VARCHAR(128),
    ip_hash VARCHAR(128),
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE user_sessions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    session_token_hash VARCHAR(255) NOT NULL UNIQUE,
    device_id VARCHAR(128),
    source_qr_code VARCHAR(128),
    expires_at TIMESTAMPTZ NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    revoked_at TIMESTAMPTZ
);

CREATE TABLE consent_versions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    version VARCHAR(32) NOT NULL,
    language VARCHAR(8) NOT NULL DEFAULT 'fa',
    title TEXT NOT NULL,
    body TEXT NOT NULL,
    status record_status NOT NULL DEFAULT 'draft',
    effective_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE(version, language)
);

CREATE TABLE consent_logs (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    session_id UUID REFERENCES user_sessions(id) ON DELETE SET NULL,
    consent_version_id UUID NOT NULL REFERENCES consent_versions(id),
    source VARCHAR(64) NOT NULL DEFAULT 'pwa',
    venue_id UUID,
    accepted_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    ip_hash VARCHAR(128),
    device_id VARCHAR(128)
);

CREATE TABLE venues (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    code VARCHAR(64) NOT NULL UNIQUE,
    name TEXT NOT NULL,
    city TEXT,
    status record_status NOT NULL DEFAULT 'draft',
    profile_status record_status NOT NULL DEFAULT 'draft',
    metadata JSONB NOT NULL DEFAULT '{}'::jsonb,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE zones (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    venue_id UUID NOT NULL REFERENCES venues(id) ON DELETE CASCADE,
    code VARCHAR(64) NOT NULL,
    name TEXT NOT NULL,
    status record_status NOT NULL DEFAULT 'draft',
    metadata JSONB NOT NULL DEFAULT '{}'::jsonb,
    UNIQUE(venue_id, code)
);

CREATE TABLE hubs (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    zone_id UUID NOT NULL REFERENCES zones(id) ON DELETE CASCADE,
    code VARCHAR(64) NOT NULL,
    name TEXT NOT NULL,
    hub_type VARCHAR(64) NOT NULL,
    status record_status NOT NULL DEFAULT 'draft',
    metadata JSONB NOT NULL DEFAULT '{}'::jsonb,
    UNIQUE(zone_id, code)
);

CREATE TABLE campaigns (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    venue_id UUID NOT NULL REFERENCES venues(id) ON DELETE CASCADE,
    code VARCHAR(64) NOT NULL,
    name TEXT NOT NULL,
    campaign_type VARCHAR(64) NOT NULL DEFAULT 'treasure_hunt',
    status record_status NOT NULL DEFAULT 'draft',
    start_at TIMESTAMPTZ,
    end_at TIMESTAMPTZ,
    metadata JSONB NOT NULL DEFAULT '{}'::jsonb,
    UNIQUE(venue_id, code)
);

CREATE TABLE touchpoints (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    hub_id UUID NOT NULL REFERENCES hubs(id) ON DELETE CASCADE,
    code VARCHAR(64) NOT NULL,
    label TEXT NOT NULL,
    type VARCHAR(64) NOT NULL,
    owner_type VARCHAR(64) DEFAULT 'venue',
    status record_status NOT NULL DEFAULT 'draft',
    install_notes TEXT,
    metadata JSONB NOT NULL DEFAULT '{}'::jsonb,
    UNIQUE(hub_id, code)
);

CREATE TABLE media_points (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    touchpoint_id UUID NOT NULL REFERENCES touchpoints(id) ON DELETE CASCADE,
    media_type VARCHAR(64) NOT NULL,
    asset_id VARCHAR(128),
    status record_status NOT NULL DEFAULT 'draft',
    metadata JSONB NOT NULL DEFAULT '{}'::jsonb
);

CREATE TABLE merchants (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    venue_id UUID NOT NULL REFERENCES venues(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    package_tier VARCHAR(64) DEFAULT 'basic',
    status record_status NOT NULL DEFAULT 'draft',
    contact_json JSONB NOT NULL DEFAULT '{}'::jsonb,
    metadata JSONB NOT NULL DEFAULT '{}'::jsonb
);

CREATE TABLE merchant_nodes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    merchant_id UUID NOT NULL REFERENCES merchants(id) ON DELETE CASCADE,
    touchpoint_id UUID NOT NULL REFERENCES touchpoints(id) ON DELETE CASCADE,
    node_type VARCHAR(64) DEFAULT 'storefront',
    status record_status NOT NULL DEFAULT 'draft',
    metadata JSONB NOT NULL DEFAULT '{}'::jsonb,
    UNIQUE(merchant_id, touchpoint_id)
);

CREATE TABLE qr_codes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    code VARCHAR(128) NOT NULL UNIQUE,
    venue_id UUID NOT NULL REFERENCES venues(id),
    touchpoint_id UUID NOT NULL REFERENCES touchpoints(id),
    campaign_id UUID NOT NULL REFERENCES campaigns(id),
    destination_url TEXT NOT NULL,
    label TEXT,
    status record_status NOT NULL DEFAULT 'draft',
    valid_from TIMESTAMPTZ,
    valid_until TIMESTAMPTZ,
    max_scans_per_user_per_window INTEGER DEFAULT 1,
    duplicate_window_seconds INTEGER DEFAULT 300,
    metadata JSONB NOT NULL DEFAULT '{}'::jsonb,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE scan_events (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    qr_code_id UUID NOT NULL REFERENCES qr_codes(id),
    venue_id UUID NOT NULL REFERENCES venues(id),
    touchpoint_id UUID NOT NULL REFERENCES touchpoints(id),
    campaign_id UUID NOT NULL REFERENCES campaigns(id),
    user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    session_id UUID REFERENCES user_sessions(id) ON DELETE SET NULL,
    result scan_result NOT NULL,
    risk_flag BOOLEAN NOT NULL DEFAULT false,
    risk_reason TEXT,
    ip_hash VARCHAR(128),
    device_id VARCHAR(128),
    user_agent_hash VARCHAR(128),
    payload_json JSONB NOT NULL DEFAULT '{}'::jsonb,
    scanned_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE event_log (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    event_type VARCHAR(128) NOT NULL,
    actor_user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    session_id UUID REFERENCES user_sessions(id) ON DELETE SET NULL,
    venue_id UUID REFERENCES venues(id) ON DELETE SET NULL,
    touchpoint_id UUID REFERENCES touchpoints(id) ON DELETE SET NULL,
    campaign_id UUID REFERENCES campaigns(id) ON DELETE SET NULL,
    object_type VARCHAR(64),
    object_id UUID,
    payload_json JSONB NOT NULL DEFAULT '{}'::jsonb,
    occurred_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE offline_scan_queue (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    source VARCHAR(64) NOT NULL DEFAULT 'pwa',
    payload_json JSONB NOT NULL,
    sync_status sync_status NOT NULL DEFAULT 'queued',
    sync_attempts INTEGER NOT NULL DEFAULT 0,
    last_error TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    synced_at TIMESTAMPTZ
);

CREATE TABLE issue_logs (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    issue_type VARCHAR(64) NOT NULL,
    severity VARCHAR(32) NOT NULL DEFAULT 'medium',
    status issue_status NOT NULL DEFAULT 'open',
    user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    session_id UUID REFERENCES user_sessions(id) ON DELETE SET NULL,
    venue_id UUID REFERENCES venues(id) ON DELETE SET NULL,
    touchpoint_id UUID REFERENCES touchpoints(id) ON DELETE SET NULL,
    description TEXT,
    metadata JSONB NOT NULL DEFAULT '{}'::jsonb,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    resolved_at TIMESTAMPTZ
);

CREATE TABLE audit_logs (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    actor_id UUID,
    action VARCHAR(128) NOT NULL,
    object_type VARCHAR(64) NOT NULL,
    object_id UUID,
    before_json JSONB,
    after_json JSONB,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX idx_qr_codes_code ON qr_codes(code);
CREATE INDEX idx_scan_events_qr_time ON scan_events(qr_code_id, scanned_at DESC);
CREATE INDEX idx_scan_events_venue_time ON scan_events(venue_id, scanned_at DESC);
CREATE INDEX idx_event_log_type_time ON event_log(event_type, occurred_at DESC);
CREATE INDEX idx_touchpoints_status ON touchpoints(status);
CREATE INDEX idx_users_mobile_hash ON users(mobile_hash);

-- Seed venue placeholders
INSERT INTO venues (code, name, city, status, profile_status) VALUES
('ECOPARK_ABBASABAD', 'EcoPark Abbas Abad', 'Tehran', 'draft', 'draft'),
('ERAM_PARK', 'Eram Amusement Park', 'Tehran', 'draft', 'draft'),
('MILAD_TOWER', 'Milad Tower', 'Tehran', 'placeholder', 'placeholder')
ON CONFLICT (code) DO NOTHING;
