import 'package:flutter/material.dart';

class PremiumSearchBar extends StatelessWidget {
  const PremiumSearchBar({super.key, this.controller, this.hintText = 'Search...', this.onChanged, this.onSubmitted, this.onClear, this.focusNode});
  final TextEditingController? controller;
  final String hintText;
  final ValueChanged<String>? onChanged;
  final ValueChanged<String>? onSubmitted;
  final VoidCallback? onClear;
  final FocusNode? focusNode;

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller,
      focusNode: focusNode,
      onChanged: onChanged,
      onSubmitted: onSubmitted,
      decoration: InputDecoration(
        hintText: hintText,
        prefixIcon: const Icon(Icons.search_rounded),
        suffixIcon: controller != null && controller!.text.isNotEmpty
            ? IconButton(onPressed: onClear ?? controller!.clear, icon: const Icon(Icons.close_rounded))
            : null,
      ),
    );
  }
}
