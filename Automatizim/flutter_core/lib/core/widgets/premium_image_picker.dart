import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

class PremiumImagePicker extends StatelessWidget {
  const PremiumImagePicker({super.key, this.path, this.currentUrl, required this.onPicked, this.height = 160, this.label = 'Upload image'});
  final String? path;
  final String? currentUrl;
  final ValueChanged<String> onPicked;
  final double height;
  final String label;

  Future<void> _pick() async {
    final file = await ImagePicker().pickImage(source: ImageSource.gallery, imageQuality: 88);
    if (file != null) onPicked(file.path);
  }

  @override Widget build(BuildContext context) => InkWell(onTap: _pick, borderRadius: BorderRadius.circular(20), child: Container(height: height, width: double.infinity, decoration: BoxDecoration(borderRadius: BorderRadius.circular(20), border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), color: Theme.of(context).colorScheme.surfaceContainerHighest.withValues(alpha: .25)), clipBehavior: Clip.antiAlias, child: path != null ? Image.file(File(path!), fit: BoxFit.cover) : currentUrl != null && currentUrl!.isNotEmpty ? Image.network(currentUrl!, fit: BoxFit.cover, errorBuilder: (_, __, ___) => _placeholder(context)) : _placeholder(context)));

  Widget _placeholder(BuildContext context) => Column(mainAxisAlignment: MainAxisAlignment.center, children: [Icon(Icons.add_a_photo_outlined, size: 34, color: Theme.of(context).colorScheme.primary), const SizedBox(height: 8), Text(label, style: const TextStyle(fontWeight: FontWeight.w700)), const SizedBox(height: 3), Text('PNG, JPG', style: TextStyle(fontSize: 11, color: Theme.of(context).colorScheme.onSurfaceVariant))]);
}
