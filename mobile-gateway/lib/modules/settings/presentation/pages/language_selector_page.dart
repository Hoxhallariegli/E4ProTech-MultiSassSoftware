import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../core/localization/locale_cubit.dart';
import '../../../../core/widgets/premium_widgets.dart';
import '../../data/language_repository.dart';

class LanguageSelectorPage extends StatefulWidget {
  const LanguageSelectorPage({super.key});

  @override
  State<LanguageSelectorPage> createState() => _LanguageSelectorPageState();
}

class _LanguageSelectorPageState extends State<LanguageSelectorPage> {
  final _repository = LanguageRepository();
  List<Map<String, dynamic>> _languages = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadLanguages();
  }

  Future<void> _loadLanguages() async {
    try {
      final data = await _repository.getLanguages();
      if (mounted) {
        setState(() {
          _languages = data;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error loading languages: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      backgroundColor: theme.colorScheme.surface,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Language Management', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
            Text('Select your preferred interface language', style: TextStyle(fontSize: 11, color: Colors.grey)),
          ],
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator.adaptive())
          : ListView(
              padding: const EdgeInsets.all(20),
              children: [
                const Row(
                  children: [
                    Icon(Icons.language_rounded, color: Colors.blueAccent, size: 20),
                    const SizedBox(width: 8),
                    const Text('Available Languages', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
                  ],
                ),
                const SizedBox(height: 18),
                ..._languages.map((lang) => _buildLanguageTile(context, lang)),
                const SizedBox(height: 30),
                Text(
                  'Note: Changing the language will update the UI labels across all dynamic modules instantly.',
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade600, fontStyle: FontStyle.italic),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
    );
  }

  Widget _buildLanguageTile(BuildContext context, Map<String, dynamic> lang) {
    final code = lang['code'] ?? 'en';
    final name = lang['name'] ?? code.toUpperCase();

    return BlocBuilder<LocaleCubit, Locale>(
      builder: (context, locale) {
        final isSelected = locale.languageCode == code;

        return Container(
          margin: const EdgeInsets.only(bottom: 12),
          decoration: BoxDecoration(
            color: isSelected
                ? Theme.of(context).colorScheme.primary.withOpacity(0.08)
                : Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(0.3),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
              color: isSelected ? Theme.of(context).colorScheme.primary : Theme.of(context).colorScheme.outlineVariant.withOpacity(0.5),
              width: isSelected ? 2 : 1,
            ),
          ),
          child: ListTile(
            onTap: () {
              context.read<LocaleCubit>().setLocale(code);
              Navigator.pop(context);
            },
            leading: Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: isSelected ? Theme.of(context).colorScheme.primary : Colors.grey.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Text(
                code.toUpperCase(),
                style: TextStyle(
                  color: isSelected ? Colors.white : Colors.grey,
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            title: Text(
              name,
              style: TextStyle(
                fontWeight: isSelected ? FontWeight.w900 : FontWeight.w600,
                color: isSelected ? Theme.of(context).colorScheme.primary : null,
              ),
            ),
            trailing: isSelected
                ? Icon(Icons.check_circle_rounded, color: Theme.of(context).colorScheme.primary)
                : null,
            contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 4),
          ),
        );
      },
    );
  }
}
