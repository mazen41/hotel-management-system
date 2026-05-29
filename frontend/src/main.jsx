import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { Crown, Filter, Hotel, Plus, Search, UserRound, UsersRound } from 'lucide-react';
import { guestsApi } from './api/guests';
import './styles/app.css';

const emptyGuest = {
  first_name: '', last_name: '', email: '', phone: '', country: '', city: '', address: '',
  passport_number: '', national_id: '', date_of_birth: '', notes: '', vip_status: false,
  marketing_consent: false,
};

function Sidebar() {
  return <aside className="sidebar">
    <div className="brand"><Hotel size={24} /><span>NoBeds Hotel OS</span></div>
    <nav>
      <a>Dashboard</a><a>Room Types</a><a>Rooms</a><a className="active"><UsersRound size={18} />Guests</a><a>Settings</a>
    </nav>
  </aside>;
}

function GuestForm({ guest, onCancel, onSaved }) {
  const [form, setForm] = useState(guest || emptyGuest);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const update = (key, value) => setForm((current) => ({ ...current, [key]: value }));

  async function submit(event) {
    event.preventDefault(); setSaving(true); setError('');
    try {
      const payload = { ...form, vip_status: Boolean(form.vip_status), marketing_consent: Boolean(form.marketing_consent) };
      const response = form.id ? await guestsApi.update(form.id, payload) : await guestsApi.create(payload);
      onSaved(response.data.data);
    } catch (err) {
      setError(err.response?.data?.message || 'Unable to save guest profile.');
    } finally { setSaving(false); }
  }

  return <div className="modalBackdrop"><form className="guestForm modal" onSubmit={submit}>
    <div className="modalHeader"><div><p className="eyebrow">Guest CRM</p><h2>{form.id ? 'Edit guest' : 'Create guest'}</h2></div><button type="button" onClick={onCancel}>×</button></div>
    {error && <div className="error">{error}</div>}
    <div className="grid two"><label>First name<input required value={form.first_name} onChange={(e) => update('first_name', e.target.value)} /></label><label>Last name<input required value={form.last_name} onChange={(e) => update('last_name', e.target.value)} /></label></div>
    <div className="grid two"><label>Email<input type="email" value={form.email || ''} onChange={(e) => update('email', e.target.value)} /></label><label>Phone<input value={form.phone || ''} onChange={(e) => update('phone', e.target.value)} /></label></div>
    <div className="grid three"><label>Country<input value={form.country || ''} onChange={(e) => update('country', e.target.value)} /></label><label>City<input value={form.city || ''} onChange={(e) => update('city', e.target.value)} /></label><label>Date of birth<input type="date" value={form.date_of_birth || ''} onChange={(e) => update('date_of_birth', e.target.value)} /></label></div>
    <div className="grid two"><label>Passport number<input value={form.passport_number || ''} onChange={(e) => update('passport_number', e.target.value)} /></label><label>National ID<input value={form.national_id || ''} onChange={(e) => update('national_id', e.target.value)} /></label></div>
    <label>Address<textarea value={form.address || ''} onChange={(e) => update('address', e.target.value)} /></label>
    <label>Internal notes<textarea value={form.notes || ''} onChange={(e) => update('notes', e.target.value)} /></label>
    <div className="switches"><label><input type="checkbox" checked={Boolean(form.vip_status)} onChange={(e) => update('vip_status', e.target.checked)} /> VIP guest</label><label><input type="checkbox" checked={Boolean(form.marketing_consent)} onChange={(e) => update('marketing_consent', e.target.checked)} /> Marketing consent</label></div>
    <div className="actions"><button type="button" className="ghost" onClick={onCancel}>Cancel</button><button disabled={saving}>{saving ? 'Saving…' : 'Save guest'}</button></div>
  </form></div>;
}

function GuestProfile({ guest, onEdit }) {
  if (!guest) return <section className="profile empty"><UserRound /><h3>Select a guest</h3><p>Open a profile to view identity, contact preferences, VIP status, and future reservation history.</p></section>;
  return <section className="profile">
    <div className="profileTop"><div className="avatar">{guest.first_name?.[0]}{guest.last_name?.[0]}</div><div><p className="eyebrow">Guest profile</p><h2>{guest.full_name}</h2><p>{guest.email || 'No email'} · {guest.phone || 'No phone'}</p></div>{guest.vip_status && <span className="vip"><Crown size={14}/>VIP</span>}</div>
    <div className="profileGrid"><div><span>Location</span><strong>{[guest.city, guest.country].filter(Boolean).join(', ') || '—'}</strong></div><div><span>Passport</span><strong>{guest.passport_number || '—'}</strong></div><div><span>National ID</span><strong>{guest.national_id || '—'}</strong></div><div><span>Marketing</span><strong>{guest.marketing_consent ? 'Consented' : 'Not opted in'}</strong></div></div>
    <button className="wide" onClick={() => onEdit(guest)}>Edit profile</button>
    <div className="history"><div><p className="eyebrow">Reservation history</p><h3>Ready for reservations</h3></div><p>Future direct bookings, NoBeds OTA imports, billing activity, and repeat-guest analytics will appear here once the Reservations phase is enabled.</p></div>
  </section>;
}

function App() {
  const [guests, setGuests] = useState([]); const [meta, setMeta] = useState({ current_page: 1, last_page: 1 });
  const [selected, setSelected] = useState(null); const [editing, setEditing] = useState(null); const [loading, setLoading] = useState(false);
  const [filters, setFilters] = useState({ search: '', country: '', vip_status: '' });
  const params = useMemo(() => ({ ...filters, vip_status: filters.vip_status || undefined, page: meta.current_page, per_page: 10 }), [filters, meta.current_page]);
  async function load(page = 1) { setLoading(true); const res = await guestsApi.list({ ...params, page }); setGuests(res.data.data); setMeta(res.data.meta || {}); setLoading(false); }
  useEffect(() => { load(1); }, [filters.search, filters.country, filters.vip_status]);
  async function openGuest(guest) { const res = await guestsApi.show(guest.id); setSelected(res.data.data); }
  function saved(guest) { setEditing(null); load(meta.current_page || 1); openGuest(guest); }
  return <div className="app"><Sidebar/><main className="main"><header className="hero"><div><p className="eyebrow">Phase 2</p><h1>Guest Management</h1><p>Scalable CRM profiles for direct guests, NoBeds OTA imports, loyalty, billing, and repeat-stay analytics.</p></div><button onClick={() => setEditing(emptyGuest)}><Plus size={16}/>New guest</button></header>
    <section className="toolbar"><div className="search"><Search size={18}/><input placeholder="Search name, email, phone, passport…" value={filters.search} onChange={(e) => setFilters({ ...filters, search: e.target.value })}/></div><div className="filter"><Filter size={16}/><input placeholder="Country" value={filters.country} onChange={(e) => setFilters({ ...filters, country: e.target.value })}/><select value={filters.vip_status} onChange={(e) => setFilters({ ...filters, vip_status: e.target.value })}><option value="">All guests</option><option value="true">VIP only</option><option value="false">Non-VIP</option></select></div></section>
    <div className="content"><section className="tableCard"><div className="tableHeader"><h2>Guest list</h2><span>{meta.total || 0} profiles</span></div>{loading ? <div className="loading">Loading guests…</div> : <table><thead><tr><th>Guest</th><th>Contact</th><th>Location</th><th>Status</th></tr></thead><tbody>{guests.map((guest) => <tr key={guest.id} onClick={() => openGuest(guest)} className={selected?.id === guest.id ? 'selected' : ''}><td><strong>{guest.full_name}</strong><small>{guest.passport_number || guest.national_id || 'No ID on file'}</small></td><td>{guest.email || '—'}<small>{guest.phone || '—'}</small></td><td>{[guest.city, guest.country].filter(Boolean).join(', ') || '—'}</td><td>{guest.vip_status ? <span className="vip"><Crown size={14}/>VIP</span> : <span className="standard">Standard</span>}</td></tr>)}</tbody></table>}<div className="pagination"><button disabled={(meta.current_page || 1) <= 1} onClick={() => load((meta.current_page || 1) - 1)}>Previous</button><span>Page {meta.current_page || 1} of {meta.last_page || 1}</span><button disabled={(meta.current_page || 1) >= (meta.last_page || 1)} onClick={() => load((meta.current_page || 1) + 1)}>Next</button></div></section><GuestProfile guest={selected} onEdit={setEditing}/></div>
    {editing && <GuestForm guest={editing} onCancel={() => setEditing(null)} onSaved={saved}/>}</main></div>;
}

createRoot(document.getElementById('root')).render(<App />);
