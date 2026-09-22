/*
 * gridstack (`float: false`, 12 kolon) yerlesim kurallari.
 * Dugum: { id, x, y, w, h } — hucre biriminde.
 */
export const COLUMNS = 12;

export const byPosition = (a, b) => a.y - b.y || a.x - b.x;

export function overlaps(a, b) {
    return a.x < b.x + b.w && b.x < a.x + a.w && a.y < b.y + b.h && b.y < a.y + a.h;
}

function clampNode(node) {
    node.w = Math.max(1, Math.min(COLUMNS, Math.round(Number(node.w) || 1)));
    node.h = Math.max(1, Math.round(Number(node.h) || 1));
    node.x = Math.max(0, Math.min(COLUMNS - node.w, Math.round(Number(node.x) || 0)));
    node.y = Math.max(0, Math.round(Number(node.y) || 0));

    return node;
}

// Cakisanlari asagi iter; `active` yerinde kalir.
function pushDown(nodes, active) {
    const order = nodes.filter((node) => node !== active).sort(byPosition);

    if (active) {
        order.unshift(active);
    }

    const placed = [];

    for (const node of order) {
        let hit;

        while ((hit = placed.find((other) => overlaps(other, node)))) {
            node.y = hit.y + hit.h;
        }

        placed.push(node);
    }
}

// float: false — her kart ustunde bosluk kalmayacak sekilde yukari toplanir.
function compact(nodes) {
    const placed = [];

    for (const node of [...nodes].sort(byPosition)) {
        while (node.y > 0 && ! placed.some((other) => overlaps(other, { ...node, y: node.y - 1 }))) {
            node.y -= 1;
        }

        placed.push(node);
    }
}

export function normalize(nodes) {
    nodes.forEach(clampNode);
    pushDown(nodes, null);
    compact(nodes);

    return nodes;
}

/*
 * Surukleme. Asagi surukleyince altta kalan kart bosalan yere (suruklenenin
 * ustune) cikar; yukari surukleyince ustteki kart asagi itilir.
 */
export function moveNode(nodes, active, x, y) {
    active.x = x;
    active.y = y;
    clampNode(active);

    const hits = nodes.filter((node) => node !== active && overlaps(node, active)).sort(byPosition);

    for (const other of hits) {
        const above = { ...other, y: active.y - other.h };

        if (above.y >= 0 && ! nodes.some((node) => node !== other && overlaps(node, above))) {
            other.y = above.y;
        }
    }

    pushDown(nodes, active);
    compact(nodes);

    return nodes;
}

export function resizeNode(nodes, active, w, h) {
    active.w = Math.min(w, COLUMNS - active.x);
    active.h = h;
    clampNode(active);
    pushDown(nodes, active);
    compact(nodes);

    return nodes;
}
