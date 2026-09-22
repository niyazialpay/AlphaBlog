// node scripts/grid-layout.check.mjs — dashboard yerlesim motorunun kontrolu.
import assert from 'node:assert/strict';
import { moveNode, normalize, overlaps, resizeNode } from '../resources/js/panel/Widgets/gridLayout.js';

const clone = (nodes) => nodes.map((node) => ({ ...node }));
const at = (nodes, id) => nodes.find((node) => node.id === id);

function assertValid(nodes) {
    for (const a of nodes) {
        assert.ok(a.x >= 0 && a.y >= 0 && a.x + a.w <= 12, `sinir disi: ${a.id}`);

        for (const b of nodes) {
            assert.ok(a === b || ! overlaps(a, b), `cakisma: ${a.id} / ${b.id}`);
        }
    }
}

// Eski Vue surumunun kaydettigi satir-indeksli (ust uste binen) yerlesim duzelir.
const legacy = normalize([
    { id: 'a', x: 0, y: 0, w: 6, h: 4 },
    { id: 'b', x: 6, y: 0, w: 6, h: 4 },
    { id: 'c', x: 0, y: 1, w: 3, h: 2 },
]);
assertValid(legacy);
assert.equal(at(legacy, 'c').y, 4);

// Asagi surukleme yer degistirir.
const stack = normalize([
    { id: 'a', x: 0, y: 0, w: 4, h: 2 },
    { id: 'b', x: 0, y: 2, w: 4, h: 2 },
]);
let nodes = clone(stack);
moveNode(nodes, at(nodes, 'a'), 0, 2);
assertValid(nodes);
assert.deepEqual([at(nodes, 'b').y, at(nodes, 'a').y], [0, 2]);

// Yukari surukleme ustteki karti asagi iter.
nodes = clone(stack);
moveNode(nodes, at(nodes, 'b'), 0, 0);
assertValid(nodes);
assert.deepEqual([at(nodes, 'b').y, at(nodes, 'a').y], [0, 2]);

// Bos alana yatay tasima; yukari toplanir.
nodes = clone(stack);
moveNode(nodes, at(nodes, 'b'), 6, 5);
assertValid(nodes);
assert.deepEqual([at(nodes, 'b').x, at(nodes, 'b').y, at(nodes, 'a').y], [6, 0, 0]);

// Yukseklik artinca alttaki itilir, azalinca geri cikar.
nodes = clone(stack);
resizeNode(nodes, at(nodes, 'a'), 4, 5);
assertValid(nodes);
assert.equal(at(nodes, 'b').y, 5);
resizeNode(nodes, at(nodes, 'a'), 4, 1);
assertValid(nodes);
assert.equal(at(nodes, 'b').y, 1);

// Genislik sag kenari asamaz.
nodes = normalize([{ id: 'a', x: 8, y: 0, w: 4, h: 2 }]);
resizeNode(nodes, at(nodes, 'a'), 9, 2);
assert.deepEqual([at(nodes, 'a').x, at(nodes, 'a').w], [8, 4]);

console.log('grid-layout: ok');
