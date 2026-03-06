import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['canvas', 'status', 'classification', 'babyName', 'offreId', 'parentName', 'parentEmail', 'mailOnlyButton'];

    connect() {
        this.audioContext = null;
        this.analyser = null;
        this.source = null;
        this.mediaStream = null;
        this.animationFrameId = null;
        this.scriptNode = null;

        this.apiUrl = this.element.dataset.apiUrl || '/api/cry-classify';
        this.mailOnlyUrl = this.element.dataset.mailOnlyUrl || '/api/cry-send-mail-only';
        this.minSendIntervalMs = Number(this.element.dataset.minSendIntervalMs || 1500);
        this.lastSentAtMs = 0;

        if (this.hasStatusTarget) {
            this.statusTarget.textContent = 'En attente…';
        }
    }

    disconnect() {
        this.stop();
    }

    async start() {
        if (this.audioContext) {
            return;
        }

        try {
            if (this.hasOffreIdTarget && !this.offreIdTarget.value) {
                if (this.hasStatusTarget) {
                    this.statusTarget.textContent = 'Veuillez choisir une babysitter à alerter.';
                }
                return;
            }

            if (this.hasStatusTarget) {
                this.statusTarget.textContent = 'Demande d’accès au micro…';
            }

            this.mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });

            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.analyser = this.audioContext.createAnalyser();
            this.analyser.fftSize = 2048;

            this.source = this.audioContext.createMediaStreamSource(this.mediaStream);
            this.source.connect(this.analyser);

            // Node pour récupérer l’audio brut et l’envoyer à l’API par fenêtres
            const bufferSize = 4096;
            this.scriptNode = this.audioContext.createScriptProcessor(bufferSize, 1, 1);
            this.source.connect(this.scriptNode);
            this.scriptNode.connect(this.audioContext.destination);

            let sending = false;
            this.scriptNode.onaudioprocess = async (audioProcessingEvent) => {
                const now = Date.now();
                if (now - this.lastSentAtMs < this.minSendIntervalMs) {
                    return;
                }
                if (sending) {
                    return;
                }

                const inputBuffer = audioProcessingEvent.inputBuffer;
                const channelData = inputBuffer.getChannelData(0);
                // Copie pour éviter les problèmes de réutilisation du buffer
                const float32 = new Float32Array(channelData.length);
                float32.set(channelData);

                sending = true;
                try {
                    await this.sendWindowToApi(float32);
                    this.lastSentAtMs = now;
                } catch (e) {
                    console.error('Erreur envoi audio à l’API', e);
                } finally {
                    sending = false;
                }
            };

            this.drawSpectrum();

            if (this.hasStatusTarget) {
                this.statusTarget.textContent = 'Capture en cours…';
            }
        } catch (error) {
            console.error(error);
            if (this.hasStatusTarget) {
                this.statusTarget.textContent = 'Erreur : impossible d’accéder au micro';
            }
        }
    }

    stop() {
        if (this.animationFrameId) {
            cancelAnimationFrame(this.animationFrameId);
            this.animationFrameId = null;
        }

        if (this.scriptNode) {
            this.scriptNode.disconnect();
            this.scriptNode.onaudioprocess = null;
            this.scriptNode = null;
        }

        if (this.source) {
            this.source.disconnect();
            this.source = null;
        }

        if (this.analyser) {
            this.analyser.disconnect();
            this.analyser = null;
        }

        if (this.mediaStream) {
            this.mediaStream.getTracks().forEach((track) => track.stop());
            this.mediaStream = null;
        }

        if (this.audioContext) {
            this.audioContext.close();
            this.audioContext = null;
        }

        if (this.hasStatusTarget) {
            this.statusTarget.textContent = 'Arrêté';
        }
    }

    drawSpectrum() {
        if (!this.hasCanvasTarget || !this.analyser) {
            return;
        }

        const canvas = this.canvasTarget;
        const ctx = canvas.getContext('2d');

        // Adapter la taille du canvas au pixel ratio
        const dpr = window.devicePixelRatio || 1;
        const displayWidth = canvas.clientWidth || 600;
        const displayHeight = canvas.clientHeight || 200;
        canvas.width = displayWidth * dpr;
        canvas.height = displayHeight * dpr;
        ctx.scale(dpr, dpr);

        const bufferLength = this.analyser.frequencyBinCount;
        const dataArray = new Uint8Array(bufferLength);

        const draw = () => {
            if (!this.analyser) {
                return;
            }

            this.animationFrameId = requestAnimationFrame(draw);

            this.analyser.getByteFrequencyData(dataArray);

            ctx.clearRect(0, 0, displayWidth, displayHeight);

            const barWidth = (displayWidth / bufferLength) * 2.5;
            let x = 0;

            for (let i = 0; i < bufferLength; i++) {
                const value = dataArray[i];
                const barHeight = (value / 255) * displayHeight;

                const hue = (i / bufferLength) * 240;
                ctx.fillStyle = `hsl(${hue}, 80%, 60%)`;
                ctx.fillRect(x, displayHeight - barHeight, barWidth, barHeight);

                x += barWidth + 1;
            }
        };

        draw();
    }

    async sendWindowToApi(float32Samples) {
        // Encodage PCM 16 bits little-endian pour simplifier côté serveur / modèle
        const buffer = new ArrayBuffer(float32Samples.length * 2);
        const view = new DataView(buffer);

        for (let i = 0; i < float32Samples.length; i++) {
            let s = Math.max(-1, Math.min(1, float32Samples[i]));
            s = s < 0 ? s * 0x8000 : s * 0x7fff;
            view.setInt16(i * 2, s, true);
        }

        const blob = new Blob([buffer], { type: 'audio/pcm' });

        try {
            const url = this.buildApiUrl();

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                },
                body: blob,
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            if (this.hasClassificationTarget && data && data.label) {
                const parts = [`${data.label} (${data.confidence ?? '—'})`];
                if (data.alertCreated) parts.push('alerte envoyée');
                if (data.smsSent) parts.push('SMS');
                if (data.emailBabysitterSent) parts.push('email babysitter');
                if (data.emailParentSent) parts.push('email parent');
                let text = parts.join(' — ');
                if (data.configHint) text += ' — ' + data.configHint;
                this.classificationTarget.textContent = text;
            }
        } catch (error) {
            console.error('Erreur API classification', error);
            if (this.hasClassificationTarget) {
                this.classificationTarget.textContent = 'Erreur API';
            }
        }
    }

    buildApiUrl() {
        const url = new URL(this.apiUrl, window.location.origin);
        this.appendFormParams(url);
        return url.toString();
    }

    buildMailOnlyUrl() {
        const url = new URL(this.mailOnlyUrl, window.location.origin);
        this.appendFormParams(url);
        return url.toString();
    }

    appendFormParams(url) {
        if (this.hasOffreIdTarget && this.offreIdTarget.value) {
            url.searchParams.set('offreId', this.offreIdTarget.value);
        }
        if (this.hasBabyNameTarget) {
            const babyName = (this.babyNameTarget.value || '').trim();
            if (babyName) url.searchParams.set('babyName', babyName);
        }
        if (this.hasParentNameTarget) {
            const parentName = (this.parentNameTarget.value || '').trim();
            if (parentName) url.searchParams.set('parentName', parentName);
        }
        if (this.hasParentEmailTarget) {
            const parentEmail = (this.parentEmailTarget.value || '').trim();
            if (parentEmail) url.searchParams.set('parentEmail', parentEmail);
        }
    }

    async sendMailOnly() {
        if (this.hasOffreIdTarget && !this.offreIdTarget.value) {
            if (this.hasStatusTarget) {
                this.statusTarget.textContent = 'Veuillez choisir une babysitter.';
            }
            return;
        }

        if (this.hasMailOnlyButtonTarget) {
            this.mailOnlyButtonTarget.disabled = true;
        }
        if (this.hasStatusTarget) {
            this.statusTarget.textContent = 'Envoi des emails…';
        }

        try {
            const response = await fetch(this.buildMailOnlyUrl(), {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || `HTTP ${response.status}`);
            }
            if (data.ok === false && data.error) {
                throw new Error(data.error);
            }

            const parts = [];
            if (data.emailBabysitterSent) parts.push('email babysitter envoyé');
            if (data.emailParentSent) parts.push('email parent envoyé');
            if (this.hasStatusTarget) {
                let msg = parts.length ? parts.join(' — ') : 'Aucun email envoyé (vérifiez l’email babysitter / parent).';
                if (data.configHint) msg += ' — ' + data.configHint;
                this.statusTarget.textContent = msg;
            }
            if (this.hasClassificationTarget && parts.length) {
                this.classificationTarget.textContent = 'Mailing : ' + parts.join(' — ');
            }
        } catch (e) {
            console.error('Erreur mailing', e);
            if (this.hasStatusTarget) {
                this.statusTarget.textContent = 'Erreur : ' + (e.message || 'envoi impossible');
            }
        } finally {
            if (this.hasMailOnlyButtonTarget) {
                this.mailOnlyButtonTarget.disabled = false;
            }
        }
    }
}

