<script>
(function () {
    if (window.__cpalnyaNetworkCanvasBooted) {
        return;
    }
    window.__cpalnyaNetworkCanvasBooted = true;

    var canvas = document.getElementById('terrainFieldGlobal');
    if (!canvas) {
        return;
    }
    var ctx = canvas.getContext('2d', { alpha: true });
    if (!ctx) {
        return;
    }

    var theme = 'enterprise';
    var dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
    var width = 0;
    var height = 0;
    var pointer = { x: 0.5, y: 0.5, active: false };
    var particles = [];
    var hubs = [];
    var rafId = 0;
    var lastTs = 0;

    function random(min, max) {
        return min + Math.random() * (max - min);
    }

    function lerp(a, b, t) {
        return a + (b - a) * t;
    }

    function buildPalette(mode) {
        if (mode === 'simple') {
            return {
                backgroundA: '#f7fbff',
                backgroundB: '#dce8f9',
                glowA: '39,95,213',
                glowB: '0,159,141',
                line: '53,94,159',
                node: '14,87,207',
                pulse: '255,196,86'
            };
        }
        return {
            backgroundA: '#07111f',
            backgroundB: '#0c1930',
            glowA: '122,180,255',
            glowB: '44,224,199',
            line: '120,166,255',
            node: '130,198,255',
            pulse: '255,224,122'
        };
    }

    function resize() {
        width = Math.max(window.innerWidth || 0, 320);
        height = Math.max(window.innerHeight || 0, 320);
        canvas.width = Math.round(width * dpr);
        canvas.height = Math.round(height * dpr);
        canvas.style.width = width + 'px';
        canvas.style.height = height + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        seedScene();
    }

    function seedScene() {
        var count = Math.max(24, Math.round(width / 90));
        var hubCount = Math.max(4, Math.round(width / 360));
        particles = [];
        hubs = [];
        for (var i = 0; i < count; i++) {
            particles.push({
                x: random(0, width),
                y: random(0, height),
                baseX: random(0, width),
                baseY: random(0, height),
                radius: random(1.2, 3.4),
                drift: random(0.12, 0.46),
                speed: random(0.00045, 0.00135),
                phase: random(0, Math.PI * 2),
                energy: random(0.35, 1)
            });
        }
        for (var h = 0; h < hubCount; h++) {
            hubs.push({
                x: ((h + 1) / (hubCount + 1)) * width + random(-70, 70),
                y: random(height * 0.18, height * 0.82),
                radius: random(70, 150),
                orbit: random(0.0002, 0.0007),
                phase: random(0, Math.PI * 2)
            });
        }
    }

    function drawBackdrop(palette, time) {
        var bg = ctx.createLinearGradient(0, 0, width, height);
        bg.addColorStop(0, palette.backgroundA);
        bg.addColorStop(1, palette.backgroundB);
        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, width, height);

        for (var i = 0; i < hubs.length; i++) {
            var hub = hubs[i];
            var hx = hub.x + Math.cos(time * hub.orbit + hub.phase) * 24;
            var hy = hub.y + Math.sin(time * hub.orbit + hub.phase) * 18;
            var glow = ctx.createRadialGradient(hx, hy, 0, hx, hy, hub.radius);
            glow.addColorStop(0, 'rgba(' + palette.glowA + ',0.22)');
            glow.addColorStop(0.55, 'rgba(' + palette.glowB + ',0.10)');
            glow.addColorStop(1, 'rgba(' + palette.glowB + ',0)');
            ctx.fillStyle = glow;
            ctx.beginPath();
            ctx.arc(hx, hy, hub.radius, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function drawLinks(palette, time) {
        ctx.lineWidth = 1;
        for (var i = 0; i < particles.length; i++) {
            var a = particles[i];
            for (var j = i + 1; j < particles.length; j++) {
                var b = particles[j];
                var dx = a.x - b.x;
                var dy = a.y - b.y;
                var dist = Math.sqrt(dx * dx + dy * dy);
                if (dist > 170) {
                    continue;
                }
                var alpha = (1 - dist / 170) * 0.34;
                ctx.strokeStyle = 'rgba(' + palette.line + ',' + alpha.toFixed(3) + ')';
                ctx.beginPath();
                ctx.moveTo(a.x, a.y);
                ctx.lineTo(b.x, b.y);
                ctx.stroke();
            }
        }

        for (var h = 0; h < hubs.length; h++) {
            var hub = hubs[h];
            var hx = hub.x + Math.cos(time * hub.orbit + hub.phase) * 24;
            var hy = hub.y + Math.sin(time * hub.orbit + hub.phase) * 18;
            ctx.strokeStyle = 'rgba(' + palette.glowA + ',0.12)';
            ctx.lineWidth = 1.5;
            for (var p = 0; p < particles.length; p++) {
                var particle = particles[p];
                var hubDx = particle.x - hx;
                var hubDy = particle.y - hy;
                var hubDist = Math.sqrt(hubDx * hubDx + hubDy * hubDy);
                if (hubDist > hub.radius) {
                    continue;
                }
                ctx.beginPath();
                ctx.moveTo(hx, hy);
                ctx.lineTo(particle.x, particle.y);
                ctx.stroke();
            }
        }
    }

    function drawNodes(palette, time) {
        for (var i = 0; i < particles.length; i++) {
            var particle = particles[i];
            var offsetX = Math.cos(time * particle.speed * 1000 + particle.phase) * particle.drift * 22;
            var offsetY = Math.sin(time * particle.speed * 850 + particle.phase) * particle.drift * 18;
            var pullX = (pointer.x * width - width / 2) * 0.012 * particle.energy;
            var pullY = (pointer.y * height - height / 2) * 0.012 * particle.energy;
            particle.x = lerp(particle.x, particle.baseX + offsetX + pullX, 0.08);
            particle.y = lerp(particle.y, particle.baseY + offsetY + pullY, 0.08);

            var pulse = 0.4 + Math.sin(time * 0.0022 + particle.phase) * 0.2;
            ctx.fillStyle = 'rgba(' + palette.node + ',' + (0.48 + pulse * 0.28).toFixed(3) + ')';
            ctx.beginPath();
            ctx.arc(particle.x, particle.y, particle.radius + pulse, 0, Math.PI * 2);
            ctx.fill();
        }

        for (var h = 0; h < hubs.length; h++) {
            var hub = hubs[h];
            var hx = hub.x + Math.cos(time * hub.orbit + hub.phase) * 24;
            var hy = hub.y + Math.sin(time * hub.orbit + hub.phase) * 18;
            var orbitRadius = 10 + Math.sin(time * 0.0012 + hub.phase) * 3;
            ctx.strokeStyle = 'rgba(' + palette.pulse + ',0.34)';
            ctx.lineWidth = 1.2;
            ctx.beginPath();
            ctx.arc(hx, hy, hub.radius * 0.22 + orbitRadius, 0, Math.PI * 2);
            ctx.stroke();
            ctx.fillStyle = 'rgba(' + palette.pulse + ',0.95)';
            ctx.beginPath();
            ctx.arc(hx, hy, 3.2, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function frame(ts) {
        if (!lastTs) {
            lastTs = ts;
        }
        var time = ts;
        lastTs = ts;
        var palette = buildPalette(theme);
        ctx.clearRect(0, 0, width, height);
        drawBackdrop(palette, time);
        drawLinks(palette, time);
        drawNodes(palette, time);
        rafId = window.requestAnimationFrame(frame);
    }

    function onPointerMove(event) {
        if (!event) {
            return;
        }
        pointer.active = true;
        pointer.x = Math.max(0, Math.min(1, event.clientX / Math.max(width, 1)));
        pointer.y = Math.max(0, Math.min(1, event.clientY / Math.max(height, 1)));
    }

    function onLeave() {
        pointer.active = false;
        pointer.x = 0.5;
        pointer.y = 0.5;
    }

    window.__terrainApplyTheme = function (nextTheme) {
        theme = nextTheme === 'simple' ? 'simple' : 'enterprise';
    };

    window.addEventListener('resize', resize);
    window.addEventListener('mousemove', onPointerMove, { passive: true });
    window.addEventListener('mouseleave', onLeave, { passive: true });
    window.addEventListener('blur', onLeave);

    resize();
    window.cancelAnimationFrame(rafId);
    rafId = window.requestAnimationFrame(frame);
})();
</script>
