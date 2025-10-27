import React, {useState, useEffect} from 'react';
export default function App(){
const [team, setTeam] = useState('');
const [logged, setLogged] = useState(false);
useEffect(()=>{ if(localStorage.getItem('ctf_team')){ setTeam(localStorage.getItem('ctf_team')); setLogged(true);} },[]);
function login(e){ e.preventDefault(); if(!team) return; localStorage.setItem('ctf_team',team); setLogged(true); window.location='/backend/real/index.php'; }
return (
<div className="min-h-screen bg-gradient-to-r from-slate-900 to-indigo-900 text-white flex items-center justify-center">
{!logged? (
<form onSubmit={login} className="bg-gray-800 p-8 rounded-lg shadow-lg w-96">
<h2 className="text-2xl mb-4">CTF Lab — Acceso</h2>
<input value={team} onChange={e=>setTeam(e.target.value)} placeholder="Nombre de equipo" className="w-full p-2 mb-3 rounded bg-gray-700" />
<button className="w-full bg-indigo-600 p-2 rounded">Entrar</button>
</form>
) : (
<div className="p-6 bg-gray-800 rounded-lg shadow-lg w-3/4">
<h1 className="text-3xl">Panel</h1>
<p>Abre el <a className="text-indigo-300 underline" href="/backend/real/dashboard.php">dashboard</a> para ver retos y enviar flags.</p>
</div>
)}
</div>
);
}