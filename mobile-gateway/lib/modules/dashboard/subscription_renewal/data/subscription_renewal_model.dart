class SubscriptionRenewalModel {
  final int? id;
  final int? planId;
  final String? paymentMethod;
  final String? transferDocument;
  final double? amount;
  final String? notes;
  final String? status;

  const SubscriptionRenewalModel({
    this.id,
    this.planId,
    this.paymentMethod,
    this.transferDocument,
    this.amount,
    this.notes,
    this.status,
  });

  factory SubscriptionRenewalModel.fromJson(Map<String, dynamic> json) => SubscriptionRenewalModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      planId: json['plan_id'] == null ? null : int.tryParse(json['plan_id'].toString()),
      paymentMethod: json['payment_method']?.toString(),
      transferDocument: json['transfer_document']?.toString(),
      amount: json['amount'] == null ? null : double.tryParse(json['amount'].toString()),
      notes: json['notes']?.toString(),
      status: json['status']?.toString(),
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'plan_id': planId,
      'payment_method': paymentMethod,
      'transfer_document': transferDocument,
      'amount': amount,
      'notes': notes,
      'status': status,
  };
}