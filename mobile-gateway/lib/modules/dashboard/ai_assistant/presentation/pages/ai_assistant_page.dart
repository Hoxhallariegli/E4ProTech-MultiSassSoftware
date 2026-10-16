import 'dart:convert';
import 'package:flutter/material.dart';
import '../../../../../services/api_service.dart';

class AiAssistantPage extends StatefulWidget {
  const AiAssistantPage({super.key});

  @override
  State<AiAssistantPage> createState() => _AiAssistantPageState();
}

class _AiAssistantPageState extends State<AiAssistantPage> {
  final _promptController = TextEditingController();
  bool _isProcessing = false;
  final List<Map<String, dynamic>> _history = [];

  Future<void> _process() async {
    final prompt = _promptController.text.trim();
    if (prompt.length < 2 || _isProcessing) return;

    setState(() {
      _isProcessing = true;
    });

    try {
      final res = await ApiService.post('/ai-assistant', {'prompt': prompt});
      if (res.statusCode == 200 || res.statusCode == 201) {
        final data = jsonDecode(res.body);

        if (data['require_confirmation'] == true && mounted) {
          setState(() => _isProcessing = false);
          _showConfirmationDialog(prompt, data);
          return;
        }

        setState(() {
          _history.insert(0, {
            'type': 'success',
            'prompt': prompt,
            'time': DateTime.now().toString().substring(11, 16),
            'results': data['results'] ?? [],
          });
          _promptController.clear();
        });
      } else {
        final err = ApiService.extractErrorMessage(res);
        setState(() {
          _history.insert(0, {
            'type': 'error',
            'prompt': prompt,
            'time': DateTime.now().toString().substring(11, 16),
            'message': err,
          });
        });
      }
    } catch (e) {
      setState(() {
        _history.insert(0, {
          'type': 'error',
          'prompt': prompt,
          'time': DateTime.now().toString().substring(11, 16),
          'message': e.toString(),
        });
      });
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  Future<void> _showConfirmationDialog(String prompt, Map<String, dynamic> data) async {
    final greeting = data['greeting']?.toString() ?? 'A dëshironi të procedojmë me kryerjen e këtyre veprimeve?';
    final previews = (data['previews'] as List?) ?? [];
    final actions = data['actions'];

    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Row(
          children: const [
            Icon(Icons.psychology_rounded, color: Color(0xFF7C5CFC), size: 28),
            SizedBox(width: 8),
            Expanded(child: Text('Kërkohet Konfirmimi', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16))),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(greeting, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              const SizedBox(height: 12),
              ...previews.map((p) {
                final items = (p['items'] as List?) ?? [];
                return Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFF7C5CFC).withOpacity(0.08),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFF7C5CFC).withOpacity(0.2)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(p['title']?.toString() ?? '', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 12, color: Color(0xFF7C5CFC))),
                      const SizedBox(height: 4),
                      ...items.map((it) => Padding(
                            padding: const EdgeInsets.symmetric(vertical: 1),
                            child: Text('• ${it.toString()}', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                          )),
                    ],
                  ),
                );
              }),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('❌ Jo, Anulo', style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold)),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF7C5CFC),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: const Text('✅ Po, Konfirmo', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900)),
          ),
        ],
      ),
    );

    if (confirm == true) {
      setState(() => _isProcessing = true);
      try {
        final res = await ApiService.post('/ai-assistant', {
          'prompt': prompt,
          'confirm': true,
          'actions': actions,
        });

        if (res.statusCode == 200 || res.statusCode == 201) {
          final resData = jsonDecode(res.body);
          setState(() {
            _history.insert(0, {
              'type': 'success',
              'prompt': prompt,
              'time': DateTime.now().toString().substring(11, 16),
              'results': resData['results'] ?? [],
            });
            _promptController.clear();
          });
        }
      } catch (e) {
        setState(() {
          _history.insert(0, {
            'type': 'error',
            'prompt': prompt,
            'time': DateTime.now().toString().substring(11, 16),
            'message': e.toString(),
          });
        });
      } finally {
        if (mounted) setState(() => _isProcessing = false);
      }
    } else {
      setState(() {
        _history.insert(0, {
          'type': 'error',
          'prompt': prompt,
          'time': DateTime.now().toString().substring(11, 16),
          'message': 'Kërkesa u anulua nga përdoruesi. Asnjë e dhënë nuk u ndryshua.',
        });
      });
    }
  }

  void _usePreset(String preset) {
    setState(() {
      _promptController.text = preset;
    });
  }

  @override
  void dispose() {
    _promptController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final primaryColor = const Color(0xFF7C5CFC);
    final emeraldColor = const Color(0xFF10B981);

    return Scaffold(
      appBar: AppBar(
        title: const Text('🤖 AI Assistant', style: TextStyle(fontWeight: FontWeight.w900)),
        centerTitle: false,
      ),
      body: Column(
        children: [
          // Prompt Input Card
          Container(
            padding: const EdgeInsets.all(16),
            color: isDark ? const Color(0xFF1E222D) : Colors.white,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(Icons.psychology_rounded, color: Color(0xFF7C5CFC), size: 20),
                    const SizedBox(width: 8),
                    Text(
                      'AI Operational Assistant',
                      style: TextStyle(
                        fontWeight: FontWeight.w900,
                        fontSize: 13,
                        color: isDark ? Colors.white : Colors.black87,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: _promptController,
                  maxLines: 3,
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                  decoration: InputDecoration(
                    hintText: 'p.sh: Më krijo 5 shërbime: Prerje 1000Lekë 30min...',
                    hintStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.normal),
                    filled: true,
                    fillColor: isDark ? const Color(0xFF12151C) : const Color(0xFFF8FAFC),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(16),
                      borderSide: BorderSide(color: Colors.grey.withOpacity(0.3)),
                    ),
                  ),
                ),
                const SizedBox(height: 10),
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton.icon(
                    onPressed: _isProcessing ? null : _process,
                    icon: _isProcessing
                        ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Icon(Icons.rocket_launch_rounded, color: Colors.white, size: 18),
                    label: Text(
                      _isProcessing ? 'Duke përpunuar...' : 'Ekzekuto nga AI 🚀',
                      style: const TextStyle(fontWeight: FontWeight.w900, color: Colors.white),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: primaryColor,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Preset Chips
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  ActionChip(
                    avatar: const Icon(Icons.content_cut_rounded, size: 14, color: Colors.blue),
                    label: const Text('✂️ 5 Shërbime', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                    onPressed: () => _usePreset('Më krijo 5 shërbime: Prerje 1000Lekë 30min, Mjekërr 500Lekë 15min, Larje 300Lekë 10min'),
                  ),
                  const SizedBox(width: 8),
                  ActionChip(
                    avatar: const Icon(Icons.calendar_today_rounded, size: 14, color: Colors.amber),
                    label: const Text('📅 5 Rezervime Walk-In', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                    onPressed: () => _usePreset('Më krijo 5 rezervime walk-in me klientë rastësorë për sot nga ora 13:00 deri 17:00 pa dërguar mesazhe SMS'),
                  ),
                  const SizedBox(width: 8),
                  ActionChip(
                    avatar: Icon(Icons.people_rounded, size: 14, color: emeraldColor),
                    label: const Text('👥 3 Staf', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                    onPressed: () => _usePreset('Më shto 3 pjesëtarë stafi: Ardit Berber, Klara Stylist, Egli Hoxha'),
                  ),
                ],
              ),
            ),
          ),

          const Divider(height: 1),

          // History List
          Expanded(
            child: _history.isEmpty
                ? const Center(
                    child: Text(
                      'Jepni një udhëzim për AI Assistant më sipër.',
                      style: TextStyle(color: Colors.grey, fontWeight: FontWeight.bold, fontSize: 12),
                    ),
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _history.length,
                    itemBuilder: (context, index) {
                      final item = _history[index];
                      final isSuccess = item['type'] == 'success';
                      final results = (item['results'] as List?) ?? [];

                      return Container(
                        margin: const EdgeInsets.only(bottom: 16),
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: isDark ? const Color(0xFF1E222D) : Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: isSuccess ? emeraldColor.withOpacity(0.4) : Colors.red.withOpacity(0.4)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Expanded(
                                  child: Text(
                                    '"${item['prompt']}"',
                                    style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13),
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                  decoration: BoxDecoration(color: Colors.grey.withOpacity(0.15), borderRadius: BorderRadius.circular(8)),
                                  child: Text(item['time'] ?? '', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                                ),
                              ],
                            ),
                            const SizedBox(height: 10),
                            if (isSuccess)
                              ...results.map((res) {
                                final items = (res['items'] as List?) ?? [];
                                return Container(
                                  margin: const EdgeInsets.only(top: 8),
                                  padding: const EdgeInsets.all(10),
                                  decoration: BoxDecoration(
                                    color: isDark ? const Color(0xFF12151C) : const Color(0xFFF8FAFC),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        res['title']?.toString() ?? '',
                                        style: TextStyle(fontWeight: FontWeight.w900, fontSize: 12, color: primaryColor),
                                      ),
                                      const SizedBox(height: 6),
                                      ...items.map((it) => Padding(
                                            padding: const EdgeInsets.symmetric(vertical: 2),
                                            child: Row(
                                              children: [
                                                Icon(Icons.check_circle_rounded, color: emeraldColor, size: 14),
                                                const SizedBox(width: 6),
                                                Expanded(
                                                  child: Text(
                                                    it.toString(),
                                                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                                                  ),
                                                ),
                                              ],
                                            ),
                                          )),
                                    ],
                                  ),
                                );
                              })
                            else
                              Text(
                                '⚠️ ${item['message']}',
                                style: const TextStyle(color: Colors.red, fontWeight: FontWeight.bold, fontSize: 12),
                              ),
                          ],
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}
