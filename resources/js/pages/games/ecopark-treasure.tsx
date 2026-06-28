import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, BadgeCheck, CheckCircle2, Compass, Gift, Home, MapPin, QrCode, Sparkles, Trophy, WalletCards } from 'lucide-react';
import { useMemo, useState } from 'react';

type StartMode = 'home' | 'onsite';
type NodeId = 'gate' | 'hologram' | 'ravaq' | 'food' | 'mina' | 'ocean' | 'final';

type TreasureNode = {
    id: NodeId;
    title: string;
    place: string;
    clue: string;
    mission: string;
    reward: string;
    points: number;
    x: string;
    y: string;
    accent: string;
};

const nodes: TreasureNode[] = [
    {
        id: 'gate',
        title: 'EcoPark Gate',
        place: 'Arrival point',
        clue: 'Your path starts where the first campaign message or QR is seen.',
        mission: 'Choose a route and collect the first digital seal.',
        reward: 'Starter seal + fast-entry code',
        points: 60,
        x: '12%',
        y: '74%',
        accent: 'bg-emerald-400',
    },
    {
        id: 'hologram',
        title: 'Hologram Backpack',
        place: 'Mobile display touchpoint',
        clue: 'A moving display gives the first live clue when the visitor is on site.',
        mission: 'Scan the field QR or continue from the home-start code.',
        reward: 'Welcome bonus + route unlock',
        points: 80,
        x: '25%',
        y: '48%',
        accent: 'bg-cyan-300',
    },
    {
        id: 'ravaq',
        title: 'Ravaq Commercial Hub',
        place: 'Shops and cultural units',
        clue: 'The next clue is hidden inside a partner offer, not only on the map.',
        mission: 'Visit a partner unit and claim or save a coupon.',
        reward: 'Shop coupon + loyalty score',
        points: 140,
        x: '43%',
        y: '66%',
        accent: 'bg-amber-300',
    },
    {
        id: 'food',
        title: 'Taste Tour',
        place: 'Food garden and cafes',
        clue: 'A flavor vote opens the next part of the route.',
        mission: 'Pick a food stop, vote, and collect a taste badge.',
        reward: 'Small treat + silver basket chance',
        points: 130,
        x: '57%',
        y: '38%',
        accent: 'bg-rose-300',
    },
    {
        id: 'mina',
        title: 'Gonbad Mina',
        place: 'Science and learning hub',
        clue: 'The star clue opens only after a short learning answer.',
        mission: 'Answer one short science question and unlock a star seal.',
        reward: 'Learning badge + family bonus',
        points: 170,
        x: '72%',
        y: '56%',
        accent: 'bg-violet-300',
    },
    {
        id: 'ocean',
        title: 'Ocean Park Family Route',
        place: 'Family discovery path',
        clue: 'The family passport needs one more seal before the final treasure.',
        mission: 'Complete one family-friendly stop or photo memory.',
        reward: 'Family pass + gold basket chance',
        points: 220,
        x: '84%',
        y: '27%',
        accent: 'bg-sky-300',
    },
    {
        id: 'final',
        title: 'Final Treasure',
        place: 'Campaign reward vault',
        clue: 'The final code is created from your path and can continue in the real visit.',
        mission: 'Collect enough seals and receive the continuation code.',
        reward: 'Gold basket or sponsor draw entry',
        points: 260,
        x: '91%',
        y: '78%',
        accent: 'bg-yellow-300',
    },
];

const baskets = [
    { level: 'Starter', items: ['digital seal', 'route code', 'first points'] },
    { level: 'Silver', items: ['shop coupon', 'taste reward', 'small draw chance'] },
    { level: 'Gold', items: ['Ravaq + food basket', 'family invite', 'double onsite points'] },
    { level: 'Legendary', items: ['sponsor prize', 'VIP visit pack', 'public winner badge'] },
];

function formatFa(value: number) {
    return value.toLocaleString('fa-IR');
}

function buildCode(mode: StartMode, completed: NodeId[]) {
    const prefix = mode === 'home' ? 'HOME' : 'PARK';
    const score = completed.length * 17 + (mode === 'home' ? 41 : 64);
    return `EXP-${prefix}-1405-${score}`;
}

export default function EcoParkTreasureGame() {
    const [mode, setMode] = useState<StartMode>('home');
    const [selected, setSelected] = useState<NodeId>('gate');
    const [completed, setCompleted] = useState<NodeId[]>([]);

    const selectedNode = nodes.find((node) => node.id === selected) ?? nodes[0];
    const points = useMemo(
        () => nodes.filter((node) => completed.includes(node.id)).reduce((sum, node) => sum + node.points, 0),
        [completed],
    );
    const code = buildCode(mode, completed);
    const progress = Math.round((completed.length / nodes.length) * 100);

    function completeNode(id: NodeId) {
        setSelected(id);
        setCompleted((items) => (items.includes(id) ? items : [...items, id]));
    }

    return (
        <>
            <Head title="EcoPark Treasure Map Game" />
            <main className="min-h-screen bg-[#f6f7f2] text-zinc-950" dir="ltr">
                <section className="border-b border-zinc-200 bg-white/90 backdrop-blur">
                    <div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-5 py-4 sm:px-8 lg:px-10">
                        <Link href="/admin/mission-blueprints" className="inline-flex items-center gap-2 text-sm font-medium text-zinc-600 hover:text-zinc-950">
                            <ArrowLeft className="size-4" />
                            Mission blueprints
                        </Link>
                        <Link href="/dashboard" className="inline-flex h-9 items-center gap-2 rounded-md border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-700 hover:bg-zinc-100">
                            <Compass className="size-4" />
                            Dashboard
                        </Link>
                    </div>
                </section>

                <section className="mx-auto grid max-w-7xl gap-6 px-5 py-7 sm:px-8 lg:grid-cols-[0.82fr_1.18fr] lg:px-10">
                    <div className="flex flex-col justify-between gap-6">
                        <div>
                            <p className="text-sm font-semibold text-emerald-700">Exploria pilot game</p>
                            <h1 className="mt-3 text-4xl font-semibold leading-tight sm:text-5xl">EcoPark Treasure Map</h1>
                            <p className="mt-4 max-w-2xl text-sm leading-7 text-zinc-600">
                                A polished first-playable prototype for the EcoPark campaign. Visitors may start at home, or start on site from a QR, hologram backpack, or display touchpoint.
                            </p>
                        </div>

                        <div className="grid gap-3 sm:grid-cols-2">
                            <button
                                type="button"
                                onClick={() => setMode('home')}
                                className={`rounded-lg border p-4 text-left transition ${mode === 'home' ? 'border-emerald-300 bg-emerald-50 text-emerald-950 shadow-sm' : 'border-zinc-200 bg-white hover:bg-zinc-50 shadow-sm'}`}
                            >
                                <Home className="size-5" />
                                <p className="mt-3 font-semibold">Start from home</p>
                                <p className="mt-1 text-xs opacity-80">Pre-game, route choice, first clue, onsite continuation code.</p>
                            </button>
                            <button
                                type="button"
                                onClick={() => setMode('onsite')}
                                className={`rounded-lg border p-4 text-left transition ${mode === 'onsite' ? 'border-cyan-300 bg-cyan-50 text-cyan-950 shadow-sm' : 'border-zinc-200 bg-white hover:bg-zinc-50 shadow-sm'}`}
                            >
                                <QrCode className="size-5" />
                                <p className="mt-3 font-semibold">Start at EcoPark</p>
                                <p className="mt-1 text-xs opacity-80">QR scan, field display, route unlock, live rewards.</p>
                            </button>
                        </div>

                        <div className="grid gap-3 sm:grid-cols-3">
                            <div className="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm">
                                <p className="text-xs text-zinc-500">Progress</p>
                                <p className="mt-2 text-2xl font-semibold">{formatFa(progress)}%</p>
                            </div>
                            <div className="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm">
                                <p className="text-xs text-zinc-500">Points</p>
                                <p className="mt-2 text-2xl font-semibold">{formatFa(points)}</p>
                            </div>
                            <div className="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm">
                                <p className="text-xs text-zinc-500">Seals</p>
                                <p className="mt-2 text-2xl font-semibold">{formatFa(completed.length)} / {formatFa(nodes.length)}</p>
                            </div>
                        </div>
                    </div>

                    <div className="relative min-h-[560px] overflow-hidden rounded-lg border border-zinc-200 bg-[#eef5ed] p-4 shadow-sm">
                        <svg className="absolute inset-0 h-full w-full opacity-70" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                            <path d="M12 74 C24 48, 35 56, 43 66 S53 36, 57 38 S66 58, 72 56 S79 31, 84 27 S89 55, 91 78" fill="none" stroke="rgba(63,63,70,0.42)" strokeWidth="0.7" strokeDasharray="2 2" />
                            <path d="M8 88 C26 82, 34 88, 50 82 S73 84, 95 90" fill="none" stroke="rgba(16,185,129,0.16)" strokeWidth="8" />
                            <path d="M4 28 C28 20, 42 22, 66 15 S84 16, 98 10" fill="none" stroke="rgba(14,165,233,0.14)" strokeWidth="9" />
                        </svg>

                        {nodes.map((node, index) => {
                            const done = completed.includes(node.id);
                            const active = selected === node.id;

                            return (
                                <button
                                    key={node.id}
                                    type="button"
                                    onClick={() => setSelected(node.id)}
                                    className={`absolute z-10 flex -translate-x-1/2 -translate-y-1/2 flex-col items-center gap-2 rounded-full p-1 transition ${active ? 'scale-110' : 'hover:scale-105'}`}
                                    style={{ left: node.x, top: node.y }}
                                >
                                    <span className={`flex size-12 items-center justify-center rounded-full border text-sm font-bold shadow-lg ${done ? 'border-emerald-200 bg-emerald-300 text-zinc-950' : active ? 'border-white bg-white text-zinc-950' : `border-white/30 ${node.accent} text-zinc-950`}`}>
                                        {done ? <CheckCircle2 className="size-5" /> : index + 1}
                                    </span>
                                    <span className="max-w-28 rounded-full border border-zinc-200 bg-white/95 px-2 py-1 text-center text-[11px] font-medium leading-4 text-zinc-950 shadow-sm backdrop-blur">{node.title}</span>
                                </button>
                            );
                        })}

                        <div className="absolute bottom-4 left-4 right-4 z-20 rounded-lg border border-zinc-200 bg-white/95 p-4 text-zinc-950 shadow-lg backdrop-blur">
                            <div className="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
                                <div>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <MapPin className="size-4 text-emerald-300" />
                                        <p className="text-xs text-zinc-500">{selectedNode.place}</p>
                                    </div>
                                    <h2 className="mt-2 text-xl font-semibold text-zinc-950">{selectedNode.title}</h2>
                                    <p className="mt-2 text-sm leading-6 text-zinc-600">{selectedNode.clue}</p>
                                    <p className="mt-2 text-sm leading-6 text-zinc-900">Mission: {selectedNode.mission}</p>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => completeNode(selectedNode.id)}
                                    className="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-zinc-950 px-4 text-sm font-semibold text-white hover:bg-zinc-800"
                                >
                                    <BadgeCheck className="size-4" />
                                    Collect clue
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="mx-auto grid max-w-7xl gap-5 px-5 pb-8 sm:px-8 lg:grid-cols-[1fr_0.9fr] lg:px-10">
                    <div className="rounded-lg border border-zinc-200 bg-white p-4 text-zinc-950 shadow-sm">
                        <div className="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 pb-4">
                            <div>
                                <p className="text-sm text-zinc-500">Continuation code</p>
                                <h2 className="mt-1 font-mono text-2xl font-semibold" dir="ltr">{code}</h2>
                            </div>
                            <span className="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium">{mode === 'home' ? 'home optional start' : 'onsite start'}</span>
                        </div>
                        <div className="mt-4 grid gap-3 md:grid-cols-2">
                            {nodes.filter((node) => completed.includes(node.id)).map((node) => (
                                <div key={node.id} className="rounded-lg border border-zinc-200 p-3">
                                    <div className="flex items-center gap-2">
                                        <Gift className="size-4 text-emerald-700" />
                                        <p className="font-medium">{node.reward}</p>
                                    </div>
                                    <p className="mt-1 text-sm text-zinc-500">{node.title} · {formatFa(node.points)} points</p>
                                </div>
                            ))}
                            {completed.length === 0 && (
                                <p className="text-sm leading-7 text-zinc-500">Collected rewards will appear here after the first clue is completed.</p>
                            )}
                        </div>
                    </div>

                    <aside className="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm">
                        <div className="flex items-center gap-2">
                            <Trophy className="size-5 text-amber-600" />
                            <h2 className="font-semibold">Prize basket ladder</h2>
                        </div>
                        <div className="mt-4 grid gap-2">
                            {baskets.map((basket, index) => {
                                const unlocked = completed.length >= index + 1;
                                return (
                                    <div key={basket.level} className={`rounded-md border p-3 ${unlocked ? 'border-amber-200 bg-amber-50' : 'border-zinc-200 bg-zinc-50'}`}>
                                        <div className="flex items-center justify-between gap-3">
                                            <p className="font-medium">{basket.level}</p>
                                            {unlocked ? <WalletCards className="size-4 text-amber-700" /> : <Sparkles className="size-4 text-zinc-500" />}
                                        </div>
                                        <div className="mt-2 flex flex-wrap gap-2">
                                            {basket.items.map((item) => <span key={item} className="rounded-full bg-zinc-100 px-2 py-1 text-xs text-zinc-700">{item}</span>)}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </aside>
                </section>
            </main>
        </>
    );
}
