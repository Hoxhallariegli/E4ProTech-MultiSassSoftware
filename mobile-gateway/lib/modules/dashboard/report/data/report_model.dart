class ReportModel {
  final int? id;
  final String? periodType;
  final String? reportDate;
  final int? totalBookings;
  final double? totalRevenue;
  final int? totalCompleted;
  final int? totalCancelled;
  final String? notes;
  final int? barberId;
  final int? barberShopId;

  const ReportModel({
    this.id,
    this.periodType,
    this.reportDate,
    this.totalBookings,
    this.totalRevenue,
    this.totalCompleted,
    this.totalCancelled,
    this.notes,
    this.barberId,
    this.barberShopId,
  });

  factory ReportModel.fromJson(Map<String, dynamic> json) => ReportModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      periodType: json['period_type']?.toString(),
      reportDate: json['report_date']?.toString(),
      totalBookings: json['total_bookings'] == null ? null : int.tryParse(json['total_bookings'].toString()),
      totalRevenue: json['total_revenue'] == null ? null : double.tryParse(json['total_revenue'].toString()),
      totalCompleted: json['total_completed'] == null ? null : int.tryParse(json['total_completed'].toString()),
      totalCancelled: json['total_cancelled'] == null ? null : int.tryParse(json['total_cancelled'].toString()),
      notes: json['notes']?.toString(),
      barberId: json['barber_id'] == null ? null : int.tryParse(json['barber_id'].toString()),
      barberShopId: json['barber_shop_id'] == null ? null : int.tryParse(json['barber_shop_id'].toString()),
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'period_type': periodType,
      'report_date': reportDate,
      'total_bookings': totalBookings,
      'total_revenue': totalRevenue,
      'total_completed': totalCompleted,
      'total_cancelled': totalCancelled,
      'notes': notes,
      'barber_id': barberId,
      'barber_shop_id': barberShopId,
  };
}