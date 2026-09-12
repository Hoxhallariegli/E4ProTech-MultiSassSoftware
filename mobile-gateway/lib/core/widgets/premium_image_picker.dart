import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

class PremiumImagePicker extends StatelessWidget {
  final String label;
  final String? path;
  final String? currentUrl;
  final ValueChanged<String> onPicked;
  final double height;

  const PremiumImagePicker({
    super.key,
    required this.label,
    required this.path,
    required this.currentUrl,
    required this.onPicked,
    this.height = 190,
  });

  Future<void> _pick(BuildContext context) async {
    final picker = ImagePicker();
    final source = await showModalBottomSheet<ImageSource>(
      context: context,
      showDragHandle: true,
      builder: (_) => SafeArea(
        child: Wrap(children: [
          ListTile(leading: const Icon(Icons.photo_camera_rounded), title: const Text('Camera'), onTap: () => Navigator.pop(context, ImageSource.camera)),
          ListTile(leading: const Icon(Icons.photo_library_rounded), title: const Text('Gallery'), onTap: () => Navigator.pop(context, ImageSource.gallery)),
        ]),
      ),
    );
    if (source == null) return;
    final image = await picker.pickImage(source: source, imageQuality: 88, maxWidth: 1800, maxHeight: 1800);
    if (image != null) onPicked(image.path);
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    Widget preview;
    if (path != null && path!.isNotEmpty) {
      preview = Image.file(File(path!), fit: BoxFit.cover, width: double.infinity, height: height);
    } else if (currentUrl != null && currentUrl!.isNotEmpty) {
      preview = Image.network(currentUrl!, fit: BoxFit.cover, width: double.infinity, height: height, errorBuilder: (_, __, ___) => _placeholder(theme));
    } else {
      preview = _placeholder(theme);
    }

    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Padding(padding: const EdgeInsets.only(left: 4, bottom: 8), child: Text(label, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: theme.colorScheme.onSurfaceVariant))),
      InkWell(onTap: () => _pick(context), borderRadius: BorderRadius.circular(22), child: ClipRRect(borderRadius: BorderRadius.circular(22), child: Stack(children: [
        preview,
        Positioned.fill(child: IgnorePointer(child: DecoratedBox(decoration: BoxDecoration(gradient: LinearGradient(begin: Alignment.topCenter, end: Alignment.bottomCenter, colors: [Colors.transparent, Colors.black.withOpacity(.38)]))))),
        Positioned(left: 16, right: 16, bottom: 14, child: Row(children: [
          Container(padding: const EdgeInsets.all(10), decoration: BoxDecoration(color: Colors.black.withOpacity(.45), shape: BoxShape.circle), child: const Icon(Icons.camera_alt_rounded, color: Colors.white, size: 18)),
          const SizedBox(width: 10),
          const Expanded(child: Text('Tap to change image', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800))),
        ])),
      ]))),
    ]);
  }

  Widget _placeholder(ThemeData theme) => Container(height: height, width: double.infinity, color: theme.colorScheme.surfaceContainerHighest.withOpacity(.35), child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
    Icon(Icons.add_photo_alternate_outlined, size: 42, color: theme.colorScheme.primary),
    const SizedBox(height: 10),
    const Text('Choose an image', style: TextStyle(fontWeight: FontWeight.w800)),
    const SizedBox(height: 4),
    const Text('JPG, PNG • optimized automatically', style: TextStyle(fontSize: 11)),
  ]));
}