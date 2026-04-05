<script>
(function () {
    if (window.__cpalnyaCityCanvasBooted) {
        return;
    }
    window.__cpalnyaCityCanvasBooted = true;

    var canvas = document.getElementById('terrainFieldGlobal');
    if (!canvas) {
        return;
    }

    var ctx = canvas.getContext('2d', { alpha: true });
    if (!ctx) {
        return;
    }

    var dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
    var width = 0;
    var height = 0;
    var rafId = 0;
    var theme = 'enterprise';
    var pointer = { x: 0, y: 0, tx: 0, ty: 0, power: 0, targetPower: 0 };
    var city = {
        horizon: 0,
        avenues: [],
        rows: [],
        blocks: [],
        lamps: [],
        bridges: [],
        pulses: []
    };

    function rand(min, max) {
        return min + Math.random() * (max - min);
    }

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    function lerp(a, b, t) {
        return a + (b - a) * t;
    }

    function palette(mode) {
        if (mode === 'simple') {
            return {
                skyA: '#eff6ff',
                skyB: '#d5e4fb',
                groundA: '#dde8f9',
                groundB: '#c8d7f1',
                hazeA: '81,119,255',
                hazeB: '0,170,158',
                lineA: '61,98,190',
                lineB: '95,138,210',
                glowA: '79,120,255',
                glowB: '0,174,160',
                front: '230,238,252',
                side: '212,224,246',
                roof: '245,248,255',
                edge: '99,133,195',
                winA: '96,118,255',
                winB: '45,176,158',
                park: '174,229,204',
                lamp: '244,199,115'
            };
        }
        return {
            skyA: '#030712',
            skyB: '#081321',
            groundA: '#07101b',
            groundB: '#0b1524',
            hazeA: '102,100,255',
            hazeB: '37,225,190',
            lineA: '95,125,255',
            lineB: '25,170,156',
            glowA: '147,97,255',
            glowB: '38,233,198',
            front: '10,18,32',
            side: '14,27,47',
            roof: '18,33,58',
            edge: '122,176,255',
            winA: '124,138,255',
            winB: '43,232,196',
            park: '20,74,64',
            lamp: '255,209,126'
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
        seedCity();
    }

    function screenWarp(x, y, scale) {
        var dx = x - pointer.x;
        var dy = y - pointer.y;
        var dist = Math.sqrt(dx * dx + dy * dy) || 1;
        var reach = 180 + pointer.power * 150;
        if (dist > reach) {
            return { x: x, y: y };
        }
        var force = (1 - dist / reach);
        var push = force * force * (8 + scale * 16 + pointer.power * 20);
        return {
            x: x + (dx / dist) * push,
            y: y + (dy / dist) * push * 0.65
        };
    }

    function project(x, z, y) {
        var scale = clamp(1 - z / 1040, 0.06, 1.2);
        var sx = width * 0.5 + x * scale + (pointer.x - width * 0.5) * scale * 0.06;
        var sy = city.horizon + z * 0.82 - y * scale;
        var warped = screenWarp(sx, sy, scale);
        return { x: warped.x, y: warped.y, scale: scale };
    }

    function buildWindowPattern(rows, cols) {
        var pattern = [];
        for (var r = 0; r < rows; r++) {
            var line = [];
            for (var c = 0; c < cols; c++) {
                var roll = Math.random();
                line.push({
                    on: roll > 0.42,
                    blink: roll > 0.84 && Math.random() > 0.65,
                    tint: Math.random() > 0.5 ? 0 : 1,
                    phase: rand(0, Math.PI * 2)
                });
            }
            pattern.push(line);
        }
        return pattern;
    }

    function seedCity() {
        city.horizon = height * 0.24;
        city.avenues = [];
        city.rows = [];
        city.blocks = [];
        city.lamps = [];
        city.bridges = [];
        city.pulses = [];

        var avenueCount = width < 900 ? 7 : 9;
        for (var i = 0; i < avenueCount; i++) {
            city.avenues.push(lerp(-width * 0.56, width * 0.56, i / (avenueCount - 1)));
        }

        var depth = 0;
        while (depth < 980) {
            city.rows.push(depth);
            depth += rand(72, 120) + depth * 0.04;
        }

        for (var row = 0; row < city.rows.length - 1; row++) {
            for (var col = 0; col < city.avenues.length - 1; col++) {
                var leftEdge = city.avenues[col];
                var rightEdge = city.avenues[col + 1];
                var near = city.rows[row];
                var far = city.rows[row + 1];
                var roadW = Math.max(14, width * 0.012);
                var inset = rand(8, 16);
                var left = leftEdge + roadW + inset;
                var right = rightEdge - roadW - inset;
                var nearZ = near + roadW + inset;
                var farZ = far - roadW - inset;
                if (right - left < 20 || farZ - nearZ < 24) {
                    continue;
                }

                var isPark = Math.random() < (row < 2 ? 0.24 : 0.1);
                var baseLift = rand(-12, 24) + (row % 3 === 0 ? rand(8, 18) : 0);
                var block = {
                    left: left,
                    right: right,
                    nearZ: nearZ,
                    farZ: farZ,
                    baseLift: baseLift,
                    isPark: isPark,
                    buildings: []
                };

                if (!isPark) {
                    var colsIn = Math.max(1, Math.floor((right - left) / rand(54, 92)));
                    var rowsIn = Math.max(1, Math.floor((farZ - nearZ) / rand(56, 96)));
                    var cellW = (right - left) / colsIn;
                    var cellD = (farZ - nearZ) / rowsIn;
                    for (var rr = 0; rr < rowsIn; rr++) {
                        for (var cc = 0; cc < colsIn; cc++) {
                            if (Math.random() < 0.14) {
                                continue;
                            }
                            var bw = cellW * rand(0.46, 0.84);
                            var bd = cellD * rand(0.46, 0.82);
                            var bx = left + cc * cellW + (cellW - bw) * rand(0.08, 0.92);
                            var bz = nearZ + rr * cellD + (cellD - bd) * rand(0.08, 0.92);
                            var centerBias = 1 - Math.min(1, Math.abs((bx + bw * 0.5)) / (width * 0.34));
                            var h = rand(70, 240) + centerBias * rand(50, 180) + (1 - bz / 1000) * rand(30, 100);
                            block.buildings.push({
                                x: bx,
                                z: bz,
                                w: bw,
                                d: bd,
                                h: h,
                                baseLift: baseLift,
                                frontWindows: buildWindowPattern(Math.max(2, Math.floor(h / 34)), Math.max(2, Math.floor(bw / 18))),
                                sideWindows: buildWindowPattern(Math.max(2, Math.floor(h / 34)), Math.max(2, Math.floor(bw / 22)))
                            });
                        }
                    }
                    block.buildings.sort(function (a, b) { return b.z - a.z; });
                }
                city.blocks.push(block);
            }
        }

        for (var lr = 1; lr < city.rows.length - 1; lr++) {
            for (var la = 0; la < city.avenues.length; la++) {
                city.lamps.push({
                    x: city.avenues[la] + (la % 2 === 0 ? -10 : 10),
                    z: city.rows[lr],
                    h: rand(20, 32),
                    glow: rand(0.18, 0.3)
                });
            }
        }

        for (var br = 2; br < city.rows.length - 1; br += 4) {
            city.bridges.push({
                z: city.rows[br] + rand(-8, 8),
                left: city.avenues[1],
                right: city.avenues[city.avenues.length - 2],
                h: rand(34, 56)
            });
        }

        for (var p = 0; p < Math.max(12, city.avenues.length * 2); p++) {
            city.pulses.push({
                lane: city.avenues[Math.floor(rand(0, city.avenues.length - 0.001))] + rand(-6, 6),
                progress: Math.random(),
                speed: rand(0.00008, 0.00018),
                span: rand(48, 88),
                tint: Math.random() > 0.5 ? 0 : 1
            });
        }
    }

    function drawBackdrop(colors) {
        var sky = ctx.createLinearGradient(0, 0, 0, height);
        sky.addColorStop(0, colors.skyA);
        sky.addColorStop(0.55, colors.skyB);
        sky.addColorStop(1, colors.groundB);
        ctx.fillStyle = sky;
        ctx.fillRect(0, 0, width, height);

        var haze1 = ctx.createRadialGradient(width * 0.22, city.horizon * 0.74, 0, width * 0.22, city.horizon * 0.74, width * 0.42);
        haze1.addColorStop(0, 'rgba(' + colors.hazeA + ',0.22)');
        haze1.addColorStop(1, 'rgba(' + colors.hazeA + ',0)');
        ctx.fillStyle = haze1;
        ctx.fillRect(0, 0, width, height);

        var haze2 = ctx.createRadialGradient(width * 0.76, city.horizon * 0.92, 0, width * 0.76, city.horizon * 0.92, width * 0.4);
        haze2.addColorStop(0, 'rgba(' + colors.hazeB + ',0.18)');
        haze2.addColorStop(1, 'rgba(' + colors.hazeB + ',0)');
        ctx.fillStyle = haze2;
        ctx.fillRect(0, 0, width, height);

        ctx.fillStyle = colors.groundA;
        ctx.fillRect(0, city.horizon, width, height - city.horizon);
    }

    function drawGrid(colors, ts) {
        ctx.lineCap = 'round';
        for (var i = 0; i < city.avenues.length; i++) {
            var top = project(city.avenues[i], 0, 0);
            var bottom = project(city.avenues[i], 1060, 0);
            ctx.strokeStyle = 'rgba(' + colors.lineB + ',0.16)';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(top.x, top.y);
            ctx.lineTo(bottom.x, bottom.y);
            ctx.stroke();

            ctx.strokeStyle = 'rgba(' + colors.lineA + ',0.34)';
            ctx.lineWidth = 1.5;
            ctx.beginPath();
            ctx.moveTo(top.x + Math.sin(ts * 0.0003 + i) * 0.6, top.y);
            ctx.lineTo(bottom.x, bottom.y);
            ctx.stroke();
        }
        for (var j = 0; j < city.rows.length; j++) {
            var left = project(city.avenues[0], city.rows[j], 0);
            var right = project(city.avenues[city.avenues.length - 1], city.rows[j], 0);
            ctx.strokeStyle = 'rgba(' + colors.lineB + ',0.12)';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(left.x, left.y);
            ctx.lineTo(right.x, right.y);
            ctx.stroke();
        }
    }

    function drawPark(block, colors, ts) {
        var a = project(block.left, block.nearZ, block.baseLift);
        var b = project(block.right, block.nearZ, block.baseLift);
        var c = project(block.right, block.farZ, block.baseLift);
        var d = project(block.left, block.farZ, block.baseLift);
        ctx.fillStyle = 'rgba(' + colors.park + ',0.24)';
        ctx.strokeStyle = 'rgba(' + colors.lineB + ',0.28)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(a.x, a.y);
        ctx.lineTo(b.x, b.y);
        ctx.lineTo(c.x, c.y);
        ctx.lineTo(d.x, d.y);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();

        for (var i = 0; i < 4; i++) {
            var tx = lerp(block.left, block.right, 0.2 + i * 0.18);
            var tz = lerp(block.nearZ, block.farZ, 0.24 + (i % 2) * 0.22);
            var tree = project(tx, tz, block.baseLift + 14 + Math.sin(ts * 0.0018 + i) * 4);
            ctx.fillStyle = 'rgba(' + colors.glowB + ',0.26)';
            ctx.beginPath();
            ctx.arc(tree.x, tree.y, 2 + tree.scale * 10, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function quadPoint(bl, br, tl, tr, u, v) {
        var bx = lerp(bl.x, br.x, u);
        var by = lerp(bl.y, br.y, u);
        var tx = lerp(tl.x, tr.x, u);
        var ty = lerp(tl.y, tr.y, u);
        return { x: lerp(bx, tx, v), y: lerp(by, ty, v) };
    }

    function drawWindowPattern(pattern, bl, br, tl, tr, colors, ts) {
        for (var row = 0; row < pattern.length; row++) {
            var cols = pattern[row].length;
            for (var col = 0; col < cols; col++) {
                var slot = pattern[row][col];
                if (!slot.on) {
                    continue;
                }
                var alpha = 0.22;
                if (slot.blink) {
                    alpha += Math.max(0, Math.sin(ts * 0.00055 + slot.phase) * 0.12);
                }
                var u0 = (col + 0.18) / cols;
                var u1 = (col + 0.72) / cols;
                var v0 = (row + 0.22) / pattern.length;
                var v1 = (row + 0.78) / pattern.length;
                var p1 = quadPoint(bl, br, tl, tr, u0, v0);
                var p2 = quadPoint(bl, br, tl, tr, u1, v0);
                var p3 = quadPoint(bl, br, tl, tr, u1, v1);
                var p4 = quadPoint(bl, br, tl, tr, u0, v1);
                ctx.fillStyle = 'rgba(' + (slot.tint ? colors.winA : colors.winB) + ',' + clamp(alpha, 0.18, 0.34).toFixed(3) + ')';
                ctx.beginPath();
                ctx.moveTo(p1.x, p1.y);
                ctx.lineTo(p2.x, p2.y);
                ctx.lineTo(p3.x, p3.y);
                ctx.lineTo(p4.x, p4.y);
                ctx.closePath();
                ctx.fill();
            }
        }
    }

    function drawBuilding(building, colors, ts) {
        var x0 = building.x;
        var x1 = building.x + building.w;
        var z0 = building.z;
        var z1 = building.z + building.d;
        var base = building.baseLift || 0;
        var topHeight = building.h + base;

        var fbl = project(x0, z0, base);
        var fbr = project(x1, z0, base);
        var bbr = project(x1, z1, base);
        var tbl = project(x0, z0, topHeight);
        var tbr = project(x1, z0, topHeight);
        var tbbr = project(x1, z1, topHeight);
        var tbbl = project(x0, z1, topHeight);

        ctx.fillStyle = 'rgba(' + colors.side + ',0.76)';
        ctx.strokeStyle = 'rgba(' + colors.edge + ',0.24)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(fbr.x, fbr.y);
        ctx.lineTo(bbr.x, bbr.y);
        ctx.lineTo(tbbr.x, tbbr.y);
        ctx.lineTo(tbr.x, tbr.y);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();

        ctx.fillStyle = 'rgba(' + colors.front + ',0.88)';
        ctx.beginPath();
        ctx.moveTo(fbl.x, fbl.y);
        ctx.lineTo(fbr.x, fbr.y);
        ctx.lineTo(tbr.x, tbr.y);
        ctx.lineTo(tbl.x, tbl.y);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();

        ctx.fillStyle = 'rgba(' + colors.roof + ',0.64)';
        ctx.beginPath();
        ctx.moveTo(tbl.x, tbl.y);
        ctx.lineTo(tbr.x, tbr.y);
        ctx.lineTo(tbbr.x, tbbr.y);
        ctx.lineTo(tbbl.x, tbbl.y);
        ctx.closePath();
        ctx.fill();
        ctx.strokeStyle = 'rgba(' + colors.edge + ',0.34)';
        ctx.stroke();

        drawWindowPattern(building.frontWindows, fbl, fbr, tbl, tbr, colors, ts);
        drawWindowPattern(building.sideWindows, fbr, bbr, tbr, tbbr, colors, ts);
    }

    function drawBlocks(colors, ts) {
        for (var i = 0; i < city.blocks.length; i++) {
            var block = city.blocks[i];
            if (block.isPark) {
                drawPark(block, colors, ts);
                continue;
            }
            for (var j = 0; j < block.buildings.length; j++) {
                drawBuilding(block.buildings[j], colors, ts);
            }
        }
    }

    function drawBridges(colors) {
        for (var i = 0; i < city.bridges.length; i++) {
            var bridge = city.bridges[i];
            var a = project(bridge.left, bridge.z - 10, bridge.h);
            var b = project(bridge.right, bridge.z - 10, bridge.h);
            var c = project(bridge.right, bridge.z + 10, bridge.h);
            var d = project(bridge.left, bridge.z + 10, bridge.h);
            ctx.fillStyle = 'rgba(' + colors.side + ',0.52)';
            ctx.strokeStyle = 'rgba(' + colors.edge + ',0.34)';
            ctx.lineWidth = 1.2;
            ctx.beginPath();
            ctx.moveTo(a.x, a.y);
            ctx.lineTo(b.x, b.y);
            ctx.lineTo(c.x, c.y);
            ctx.lineTo(d.x, d.y);
            ctx.closePath();
            ctx.fill();
            ctx.stroke();
        }
    }

    function drawLamps(colors, ts) {
        for (var i = 0; i < city.lamps.length; i++) {
            var lamp = city.lamps[i];
            var base = project(lamp.x, lamp.z, 0);
            var top = project(lamp.x, lamp.z, lamp.h);
            ctx.strokeStyle = 'rgba(' + colors.edge + ',0.28)';
            ctx.lineWidth = Math.max(0.8, top.scale * 2.2);
            ctx.beginPath();
            ctx.moveTo(base.x, base.y);
            ctx.lineTo(top.x, top.y);
            ctx.stroke();

            var glow = 2 + top.scale * 16;
            var alpha = lamp.glow + Math.max(0, Math.sin(ts * 0.001 + i) * 0.04);
            ctx.fillStyle = 'rgba(' + colors.lamp + ',' + clamp(alpha, 0.16, 0.34).toFixed(3) + ')';
            ctx.beginPath();
            ctx.arc(top.x, top.y, glow, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function drawPulses(colors) {
        ctx.lineCap = 'round';
        for (var i = 0; i < city.pulses.length; i++) {
            var pulse = city.pulses[i];
            pulse.progress += pulse.speed * 16;
            if (pulse.progress > 1) {
                pulse.progress = 0;
            }
            var z = lerp(0, 1020, pulse.progress);
            var start = project(pulse.lane, Math.max(0, z - pulse.span), 0);
            var end = project(pulse.lane, Math.min(1040, z + pulse.span), 0);
            var color = pulse.tint ? colors.glowA : colors.glowB;
            var grad = ctx.createLinearGradient(start.x, start.y, end.x, end.y);
            grad.addColorStop(0, 'rgba(' + color + ',0)');
            grad.addColorStop(0.5, 'rgba(' + color + ',0.82)');
            grad.addColorStop(1, 'rgba(' + color + ',0)');
            ctx.strokeStyle = grad;
            ctx.lineWidth = 2.6;
            ctx.beginPath();
            ctx.moveTo(start.x, start.y);
            ctx.lineTo(end.x, end.y);
            ctx.stroke();
        }
    }

    function drawFallback(colors) {
        ctx.strokeStyle = 'rgba(' + colors.lineA + ',0.42)';
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.moveTo(width * 0.18, city.horizon + 20);
        ctx.lineTo(width * 0.82, height * 0.9);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(width * 0.82, city.horizon + 20);
        ctx.lineTo(width * 0.18, height * 0.9);
        ctx.stroke();
    }

    function frame(ts) {
        var colors = palette(theme);
        pointer.x = lerp(pointer.x, pointer.tx, 0.08);
        pointer.y = lerp(pointer.y, pointer.ty, 0.08);
        pointer.power = lerp(pointer.power, pointer.targetPower, 0.08);

        try {
            ctx.clearRect(0, 0, width, height);
            drawBackdrop(colors);
            drawGrid(colors, ts);
            drawBridges(colors);
            drawBlocks(colors, ts);
            drawLamps(colors, ts);
            drawPulses(colors);
            drawFallback(colors);
        } catch (error) {
            ctx.clearRect(0, 0, width, height);
            drawBackdrop(colors);
            drawFallback(colors);
        }

        rafId = window.requestAnimationFrame(frame);
    }

    function onMove(event) {
        pointer.tx = event.clientX;
        pointer.ty = event.clientY;
        pointer.targetPower = 1;
    }

    function onLeave() {
        pointer.tx = width * 0.5;
        pointer.ty = height * 0.56;
        pointer.targetPower = 0;
    }

    window.__terrainApplyTheme = function (nextTheme) {
        theme = nextTheme === 'simple' ? 'simple' : 'enterprise';
    };

    window.addEventListener('resize', resize);
    window.addEventListener('mousemove', onMove, { passive: true });
    window.addEventListener('mouseleave', onLeave, { passive: true });
    window.addEventListener('blur', onLeave);

    resize();
    onLeave();
    window.cancelAnimationFrame(rafId);
    rafId = window.requestAnimationFrame(frame);
})();
</script>
