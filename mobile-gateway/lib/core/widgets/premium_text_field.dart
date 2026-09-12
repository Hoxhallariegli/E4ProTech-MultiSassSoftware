import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

class PremiumTextField extends StatelessWidget {
  const PremiumTextField({super.key, this.controller, required this.label, this.hint, this.validator, this.keyboardType, this.maxLines = 1, this.prefixIcon, this.suffixIcon, this.readOnly = false, this.onTap, this.obscureText = false, this.textInputAction, this.inputFormatters});
  final TextEditingController? controller;
  final String label;
  final String? hint;
  final String? Function(String?)? validator;
  final TextInputType? keyboardType;
  final int maxLines;
  final IconData? prefixIcon;
  final Widget? suffixIcon;
  final bool readOnly;
  final VoidCallback? onTap;
  final bool obscureText;
  final TextInputAction? textInputAction;
  final List<TextInputFormatter>? inputFormatters;

  @override
  Widget build(BuildContext context) => TextFormField(
        controller: controller,
        validator: validator,
        keyboardType: keyboardType,
        inputFormatters: inputFormatters,
        maxLines: maxLines,
        readOnly: readOnly,
        onTap: onTap,
        obscureText: obscureText,
        textInputAction: textInputAction,
        decoration: InputDecoration(labelText: label, hintText: hint, prefixIcon: prefixIcon == null ? null : Icon(prefixIcon), suffixIcon: suffixIcon),
      );
}
