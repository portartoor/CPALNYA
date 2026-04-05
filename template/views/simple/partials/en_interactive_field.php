<script>
(function () {
    if (window.__enInteractiveFieldBooted) {
        return;
    }
    window.__enInteractiveFieldBooted = true;

    var canvas = document.getElementById('terrainFieldGlobal');
    if (!canvas) {
        canvas = document.createElement('canvas');
        canvas.id = 'terrainFieldGlobal';
        canvas.setAttribute('aria-hidden', 'true');
        document.body.insertBefore(canvas, document.body.firstChild || null);
    }
    var ctx = canvas.getContext('2d', { alpha: true });
    if (!ctx) {
        return;
    }

    var w = 0;
    var h = 0;
    var dpr = 1;
    var tick = 0;
    var nodes = [];
    var links = [];
    var packets = [];
    var starBursts = [];
    var pointer = { x: 0, y: 0, active: false, vx: 0, vy: 0 };

    function clamp(v, min, max) {
        return Math.max(min, Math.min(max, v));
    }

    function rand(min, max) {
        return min + Math.random() * (max - min);
    }

    function makeNode() {
        return {
            x: rand(0, w),
            y: rand(0, h),
            z: rand(0.2, 1.0),
            radius: rand(0.9, 2.4),
            phase: rand(0, Math.PI * 2),
            hue: rand(194, 236),
            drift: rand(0.02, 0.12)
        };
    }

    function makePacket() {
        return {
            linkIndex: -1,
            t: rand(0, 1),
            speed: rand(0.007, 0.024),
            size: rand(1.0, 2.0),
            hue: rand(198, 224)
        };
    }

    function makeBurst(x, y) {
        return {
            x: x,
            y: y,
            life: 0,
            maxLife: rand(26, 54),
            size: rand(18, 40),
            hue: rand(190, 268)
        };
    }

    function resize() {
        w = window.innerWidth || document.documentElement.clientWidth || 1280;
        h = window.innerHeight || document.documentElement.clientHeight || 720;
        dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));

        canvas.width = Math.floor(w * dpr);
        canvas.height = Math.floor(h * dpr);
        canvas.style.width = w + 'px';
        canvas.style.height = h + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        var nodeTarget = clamp(Math.floor((w * h) / 26000), 28, 82);
        var packetTarget = clamp(Math.floor((w * h) / 90000), 6, 16);

        nodes = [];
        links = [];
        packets = [];
        for (var i = 0; i < nodeTarget; i++) {
            nodes.push(makeNode());
        }
        for (var j = 0; j < packetTarget; j++) {
            packets.push(makePacket());
        }
    }

    function drawTechGrid(t) {
        var sweepX = (t * 40) % (w + 360) - 180;
        var sweep = ctx.createLinearGradient(sweepX - 140, 0, sweepX + 140, 0);
        sweep.addColorStop(0, 'rgba(80, 145, 240, 0)');
        sweep.addColorStop(0.5, 'rgba(80, 145, 240, 0.045)');
        sweep.addColorStop(1, 'rgba(80, 145, 240, 0)');
        ctx.fillStyle = sweep;
        ctx.fillRect(0, 0, w, h);
    }

    function ensurePacketLinks() {
        if (!links.length) {
            return;
        }
        for (var i = 0; i < packets.length; i++) {
            var p = packets[i];
            if (p.linkIndex < 0 || p.linkIndex >= links.length) {
                p.linkIndex = Math.floor(rand(0, links.length));
                p.t = rand(0, 1);
            }
        }
    }

    function drawNodeLinks() {
        var maxDist = 145;
        links = [];
        for (var i = 0; i < nodes.length; i++) {
            var a = nodes[i];
            for (var j = i + 1; j < nodes.length; j++) {
                var b = nodes[j];
                var dx = a.x - b.x;
                var dy = a.y - b.y;
                var dist = Math.sqrt(dx * dx + dy * dy);
                if (dist > maxDist) {
                    continue;
                }
                var alpha = (1 - dist / maxDist) * 0.12 * Math.min(a.z, b.z);
                if (pointer.active) {
                    var pmx = ((a.x + b.x) * 0.5) - pointer.x;
                    var pmy = ((a.y + b.y) * 0.5) - pointer.y;
                    var pDist = Math.sqrt(pmx * pmx + pmy * pmy);
                    if (pDist < 220) {
                        alpha *= 1.22;
                    }
                }
                ctx.strokeStyle = 'rgba(120, 190, 255,' + clamp(alpha, 0.02, 0.18).toFixed(3) + ')';
                ctx.lineWidth = clamp((a.z + b.z) * 0.6, 0.3, 1.0);
                ctx.beginPath();
                ctx.moveTo(a.x, a.y);
                ctx.lineTo(b.x, b.y);
                ctx.stroke();
                if (dist > 56) {
                    links.push({ a: a, b: b, strength: alpha });
                }
            }
        }
    }

    function drawPackets() {
        ensurePacketLinks();
        if (!links.length) {
            return;
        }
        for (var i = 0; i < packets.length; i++) {
            var p = packets[i];
            var ln = links[p.linkIndex];
            if (!ln) {
                p.linkIndex = -1;
                continue;
            }
            p.t += p.speed;
            if (p.t >= 1) {
                p.linkIndex = Math.floor(rand(0, links.length));
                p.t = 0;
                continue;
            }
            var x = ln.a.x + (ln.b.x - ln.a.x) * p.t;
            var y = ln.a.y + (ln.b.y - ln.a.y) * p.t;
            var alpha = clamp(0.16 + ln.strength * 2.2, 0.14, 0.48);

            var glow = ctx.createRadialGradient(x, y, 0, x, y, p.size * 5.2);
            glow.addColorStop(0, 'hsla(' + p.hue.toFixed(1) + ',90%,72%,' + alpha.toFixed(3) + ')');
            glow.addColorStop(1, 'hsla(' + p.hue.toFixed(1) + ',96%,54%,0)');
            ctx.fillStyle = glow;
            ctx.beginPath();
            ctx.arc(x, y, p.size * 5.2, 0, Math.PI * 2);
            ctx.fill();

            ctx.fillStyle = 'hsla(' + (p.hue + 8).toFixed(1) + ',98%,86%,' + clamp(alpha * 1.1, 0.12, 0.45).toFixed(3) + ')';
            ctx.beginPath();
            ctx.arc(x, y, p.size, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function drawNodes(t) {
        for (var i = 0; i < nodes.length; i++) {
            var n = nodes[i];
            n.x += Math.cos(n.phase + t * 0.11) * n.drift;
            n.y += Math.sin(n.phase * 1.2 + t * 0.09) * n.drift * 0.55;

            if (pointer.active) {
                var dx = n.x - pointer.x;
                var dy = n.y - pointer.y;
                var d = Math.sqrt(dx * dx + dy * dy);
                if (d < 180 && d > 0.001) {
                    var k = (1 - d / 180) * 0.26;
                    n.x += (dx / d) * k;
                    n.y += (dy / d) * k;
                }
            }

            if (n.x < -25) n.x = w + 25;
            if (n.x > w + 25) n.x = -25;
            if (n.y < -25) n.y = h + 25;
            if (n.y > h + 25) n.y = -25;

            var pulse = 0.82 + Math.sin(t * 0.25 + n.phase * 1.4) * 0.08;
            var r = n.radius * pulse * (0.8 + n.z * 0.55);

            var glow = ctx.createRadialGradient(n.x, n.y, 0, n.x, n.y, r * 4.2);
            glow.addColorStop(0, 'hsla(' + n.hue.toFixed(1) + ',88%,74%,' + (0.45 * n.z).toFixed(3) + ')');
            glow.addColorStop(1, 'hsla(' + n.hue.toFixed(1) + ',96%,50%,0)');
            ctx.fillStyle = glow;
            ctx.beginPath();
            ctx.arc(n.x, n.y, r * 4.2, 0, Math.PI * 2);
            ctx.fill();

            ctx.fillStyle = 'hsla(' + (n.hue + 16).toFixed(1) + ',92%,86%,' + clamp(0.2 + n.z * 0.26, 0.16, 0.46).toFixed(3) + ')';
            ctx.beginPath();
            ctx.arc(n.x, n.y, r, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function drawBursts() {
        for (var i = starBursts.length - 1; i >= 0; i--) {
            var b = starBursts[i];
            b.life += 1;
            var p = b.life / b.maxLife;
            if (p >= 1) {
                starBursts.splice(i, 1);
                continue;
            }
            var alpha = (1 - p) * 0.22;
            var size = b.size * (0.3 + p * 1.05);
            ctx.strokeStyle = 'hsla(' + b.hue.toFixed(1) + ',82%,70%,' + alpha.toFixed(3) + ')';
            ctx.lineWidth = clamp(1.6 - p * 1.3, 0.3, 1.6);
            ctx.beginPath();
            ctx.arc(b.x, b.y, size, 0, Math.PI * 2);
            ctx.stroke();
        }
    }

    function frame() {
        tick += 0.0085;
        ctx.clearRect(0, 0, w, h);
        drawTechGrid(tick);
        drawNodeLinks();
        drawPackets();
        drawNodes(tick);
        drawBursts();
        requestAnimationFrame(frame);
    }

    function onPointerMove(ev) {
        var nx = ev.clientX || 0;
        var ny = ev.clientY || 0;
        pointer.vx = nx - pointer.x;
        pointer.vy = ny - pointer.y;
        pointer.x = nx;
        pointer.y = ny;
        pointer.active = true;

        if (Math.abs(pointer.vx) + Math.abs(pointer.vy) > 24 && Math.random() > 0.92) {
            starBursts.push(makeBurst(pointer.x, pointer.y));
            if (starBursts.length > 22) {
                starBursts.shift();
            }
        }
    }

    function onPointerLeave() {
        pointer.active = false;
        pointer.vx = 0;
        pointer.vy = 0;
    }

    resize();
    frame();

    window.addEventListener('resize', resize);
    window.addEventListener('mousemove', onPointerMove, { passive: true });
    window.addEventListener('mouseleave', onPointerLeave, { passive: true });
})();
</script>
