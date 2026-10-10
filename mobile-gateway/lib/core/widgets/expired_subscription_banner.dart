import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../branding/branding_cubit.dart';
import '../../services/auth_service.dart';
import '../../modules/dashboard/subscription_renewal/presentation/pages/subscription_renewal_list_page.dart';

class ExpiredSubscriptionBanner extends StatelessWidget {
  const ExpiredSubscriptionBanner({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<BrandingCubit, BrandingState>(
      builder: (context, branding) {
        final isAdmin = AuthService.instance.user?['is_admin'] == true;

        if (!branding.isExpired || isAdmin) {
          return const SizedBox.shrink();
        }

        return Container(
          width: double.infinity,
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF881337), Color(0xFFBE123C)],
              begin: Alignment.centerLeft,
              end: Alignment.centerRight,
            ),
            boxShadow: [
              BoxShadow(
                color: Color(0x55BE123C),
                blurRadius: 8,
                offset: Offset(0, 3),
              ),
            ],
          ),
          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 16),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(7),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.2),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.lock_clock_rounded,
                  color: Colors.white,
                  size: 20,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Text(
                      '⚠️ Abonimi Juaj Ka Mbaruar!',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 13,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 0.2,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Rinnovoni abonimin tuaj për të hapur modulet.',
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.92),
                        fontSize: 11,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Material(
                color: Colors.white,
                borderRadius: BorderRadius.circular(8),
                child: InkWell(
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const SubscriptionRenewalListPage()),
                    );
                  },
                  borderRadius: BorderRadius.circular(8),
                  child: const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    child: Text(
                      'Renovo Tani 🚀',
                      style: TextStyle(
                        color: Color(0xFF881337),
                        fontWeight: FontWeight.w900,
                        fontSize: 11.5,
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
