import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../core/app_state.dart';
import '../core/app_strings.dart';
import '../models/models.dart';

String t(BuildContext context, String key) => AppStrings.of(context, key);

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _boot();
  }

  Future<void> _boot() async {
    await context.read<AppState>().initialize();
    final prefs = await SharedPreferences.getInstance();
    final seen = prefs.getBool('seen_onboarding') ?? false;
    if (!mounted) return;
    final state = context.read<AppState>();
    Navigator.pushReplacementNamed(
      context,
      !seen ? '/onboarding' : state.isAuthenticated ? '/dashboard' : '/home',
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.diversity_3, size: 72, color: Color(0xff227c70)),
            const SizedBox(height: 16),
            Text(t(context, 'appName'), style: Theme.of(context).textTheme.headlineMedium),
            const SizedBox(height: 12),
            const CircularProgressIndicator(),
          ],
        ),
      ),
    );
  }
}

class OnboardingScreen extends StatelessWidget {
  const OnboardingScreen({super.key});

  Future<void> _finish(BuildContext context) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('seen_onboarding', true);
    if (context.mounted) Navigator.pushReplacementNamed(context, '/home');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Spacer(),
              const Icon(Icons.volunteer_activism, size: 88, color: Color(0xff227c70)),
              const SizedBox(height: 24),
              Text(
                'منصة إنسانية لتسجيل المفقودين والموجودين والبحث عن حالات متشابهة بذكاء.',
                style: Theme.of(context).textTheme.headlineSmall,
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 16),
              const Text(
                'يتم إخفاء بيانات التواصل الحساسة ولا تظهر إلا بعد مراجعة المسؤول.',
                textAlign: TextAlign.center,
              ),
              const Spacer(),
              FilledButton.icon(
                onPressed: () => _finish(context),
                icon: const Icon(Icons.arrow_forward),
                label: const Text('ابدأ'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  late Future<DashboardStats> _stats;

  @override
  void initState() {
    super.initState();
    _stats = context.read<AppState>().api.stats();
  }

  @override
  Widget build(BuildContext context) {
    final state = context.watch<AppState>();
    return Scaffold(
      appBar: AppBar(
        title: Text(t(context, 'appName')),
        actions: [
          IconButton(
            tooltip: t(context, 'profile'),
            onPressed: () => Navigator.pushNamed(context, '/profile'),
            icon: const Icon(Icons.settings),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async => setState(() => _stats = state.api.stats()),
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Text('نساعد العائلات على الوصول إلى خيط آمن ومراجع.', style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 12),
            FutureBuilder<DashboardStats>(
              future: _stats,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) return const LinearProgressIndicator();
                if (snapshot.hasError) return ErrorPanel(message: snapshot.error.toString());
                final stats = snapshot.data!;
                return Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    StatChip(label: 'بلاغ', value: stats.requests),
                    StatChip(label: 'مفقود', value: stats.missing),
                    StatChip(label: 'موجود', value: stats.found),
                    StatChip(label: 'تطابق', value: stats.matches),
                  ],
                );
              },
            ),
            const SizedBox(height: 24),
            FilledButton.icon(
              onPressed: () => Navigator.pushNamed(context, '/search'),
              icon: const Icon(Icons.search),
              label: Text(t(context, 'search')),
            ),
            OutlinedButton.icon(
              onPressed: () => state.isAuthenticated ? Navigator.pushNamed(context, '/missing') : Navigator.pushNamed(context, '/auth'),
              icon: const Icon(Icons.person_search),
              label: Text(t(context, 'missing')),
            ),
            OutlinedButton.icon(
              onPressed: () => state.isAuthenticated ? Navigator.pushNamed(context, '/found') : Navigator.pushNamed(context, '/auth'),
              icon: const Icon(Icons.health_and_safety),
              label: Text(t(context, 'found')),
            ),
            TextButton.icon(
              onPressed: () => Navigator.pushNamed(context, state.isAuthenticated ? '/dashboard' : '/auth'),
              icon: Icon(state.isAuthenticated ? Icons.dashboard : Icons.login),
              label: Text(state.isAuthenticated ? t(context, 'dashboard') : t(context, 'login')),
            ),
          ],
        ),
      ),
    );
  }
}

class AuthScreen extends StatefulWidget {
  const AuthScreen({super.key});

  @override
  State<AuthScreen> createState() => _AuthScreenState();
}

class _AuthScreenState extends State<AuthScreen> {
  final _form = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _phone = TextEditingController();
  final _email = TextEditingController(text: 'user@example.com');
  final _password = TextEditingController(text: 'User@12345');
  final _city = TextEditingController();
  bool _register = false;
  bool _busy = false;

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _busy = true);
    try {
      final state = context.read<AppState>();
      if (_register) {
        await state.register(
          fullName: _name.text,
          phone: _phone.text,
          email: _email.text,
          password: _password.text,
          city: _city.text,
        );
      } else {
        await state.login(_email.text, _password.text);
      }
      if (mounted) Navigator.pushNamedAndRemoveUntil(context, '/dashboard', (_) => false);
    } catch (error) {
      if (mounted) showError(context, error);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(_register ? t(context, 'register') : t(context, 'login'))),
      body: Form(
        key: _form,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            SegmentedButton<bool>(
              segments: [
                ButtonSegment(value: false, label: Text(t(context, 'login')), icon: const Icon(Icons.login)),
                ButtonSegment(value: true, label: Text(t(context, 'register')), icon: const Icon(Icons.person_add)),
              ],
              selected: {_register},
              onSelectionChanged: (value) => setState(() => _register = value.first),
            ),
            const SizedBox(height: 16),
            if (_register) ...[
              AppTextField(controller: _name, label: 'الاسم الكامل', icon: Icons.badge),
              AppTextField(controller: _phone, label: 'الهاتف', icon: Icons.phone, keyboardType: TextInputType.phone),
              AppTextField(controller: _city, label: 'المدينة', icon: Icons.location_city),
            ],
            AppTextField(controller: _email, label: 'البريد الإلكتروني', icon: Icons.mail, keyboardType: TextInputType.emailAddress),
            AppTextField(controller: _password, label: 'كلمة المرور', icon: Icons.lock, obscure: true),
            const SizedBox(height: 12),
            FilledButton.icon(
              onPressed: _busy ? null : _submit,
              icon: _busy ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.check),
              label: Text(t(context, 'submit')),
            ),
          ],
        ),
      ),
    );
  }
}

class UserDashboardScreen extends StatefulWidget {
  const UserDashboardScreen({super.key});

  @override
  State<UserDashboardScreen> createState() => _UserDashboardScreenState();
}

class _UserDashboardScreenState extends State<UserDashboardScreen> {
  late Future<List<FamilyRequest>> _requests;

  @override
  void initState() {
    super.initState();
    _requests = context.read<AppState>().api.myRequests();
  }

  @override
  Widget build(BuildContext context) {
    final state = context.watch<AppState>();
    return Scaffold(
      appBar: AppBar(
        title: Text('${t(context, 'dashboard')} - ${state.account?.fullName ?? ''}'),
        actions: [
          IconButton(onPressed: () => Navigator.pushNamed(context, '/notifications'), icon: const Icon(Icons.notifications)),
          IconButton(onPressed: () => Navigator.pushNamed(context, '/profile'), icon: const Icon(Icons.person)),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async => setState(() => _requests = state.api.myRequests()),
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                FilledButton.icon(onPressed: () => Navigator.pushNamed(context, '/missing'), icon: const Icon(Icons.person_search), label: Text(t(context, 'missing'))),
                FilledButton.icon(onPressed: () => Navigator.pushNamed(context, '/found'), icon: const Icon(Icons.add_location_alt), label: Text(t(context, 'found'))),
                OutlinedButton.icon(onPressed: () => Navigator.pushNamed(context, '/search'), icon: const Icon(Icons.search), label: Text(t(context, 'search'))),
                if (state.isAdmin) OutlinedButton.icon(onPressed: () => Navigator.pushNamed(context, '/admin'), icon: const Icon(Icons.admin_panel_settings), label: Text(t(context, 'admin'))),
              ],
            ),
            const SizedBox(height: 12),
            FutureBuilder<List<FamilyRequest>>(
              future: _requests,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) return const LinearProgressIndicator();
                if (snapshot.hasError) return ErrorPanel(message: snapshot.error.toString());
                final items = snapshot.data ?? [];
                if (items.isEmpty) return EmptyPanel(message: t(context, 'noData'));
                return Column(children: items.map((item) => RequestCard(request: item)).toList());
              },
            ),
          ],
        ),
      ),
    );
  }
}

class RequestFormScreen extends StatefulWidget {
  const RequestFormScreen({required this.type, super.key});

  final String type;

  @override
  State<RequestFormScreen> createState() => _RequestFormScreenState();
}

class _RequestFormScreenState extends State<RequestFormScreen> {
  final _form = GlobalKey<FormState>();
  final Map<String, TextEditingController> _c = {
    for (final key in [
      'full_name',
      'age',
      'gender',
      'city',
      'area',
      'description',
      'health_status',
      'distinctive_marks',
      'contact_phone',
      'relationship',
      'last_known_place',
      'current_location',
      'last_seen_date',
      'registered_by_name',
    ])
      key: TextEditingController(),
  };
  String _priority = 'normal';
  File? _image;
  bool _busy = false;

  Future<void> _pickImage() async {
    final picked = await ImagePicker().pickImage(source: ImageSource.gallery, imageQuality: 85);
    if (picked != null) setState(() => _image = File(picked.path));
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _busy = true);
    try {
      final fields = {
        'request_type': widget.type,
        'priority': _priority,
        for (final entry in _c.entries) entry.key: entry.value.text,
      };
      final result = await context.read<AppState>().api.createRequest(fields, image: _image);
      if (!mounted) return;
      final id = result['request']?['request_id'] ?? result['request_id'];
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('تم إرسال البلاغ رقم $id وبدأت المطابقة التلقائية')));
      Navigator.pushReplacementNamed(context, '/dashboard');
    } catch (error) {
      if (mounted) showError(context, error);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isMissing = widget.type == 'missing';
    return Scaffold(
      appBar: AppBar(title: Text(isMissing ? t(context, 'missing') : t(context, 'found'))),
      body: Form(
        key: _form,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            AppTextField(controller: _c['full_name']!, label: 'الاسم الكامل', icon: Icons.badge),
            Row(
              children: [
                Expanded(child: AppTextField(controller: _c['age']!, label: 'العمر', icon: Icons.cake, keyboardType: TextInputType.number)),
                const SizedBox(width: 8),
                Expanded(child: AppTextField(controller: _c['gender']!, label: 'الجنس', icon: Icons.wc)),
              ],
            ),
            Row(
              children: [
                Expanded(child: AppTextField(controller: _c['city']!, label: 'المدينة', icon: Icons.location_city)),
                const SizedBox(width: 8),
                Expanded(child: AppTextField(controller: _c['area']!, label: 'المنطقة', icon: Icons.map)),
              ],
            ),
            AppTextField(controller: _c['description']!, label: 'الوصف', icon: Icons.notes, maxLines: 3),
            AppTextField(controller: _c['health_status']!, label: 'الحالة الصحية', icon: Icons.health_and_safety, required: false),
            AppTextField(controller: _c['distinctive_marks']!, label: 'علامات مميزة', icon: Icons.visibility, required: false),
            AppTextField(controller: _c['contact_phone']!, label: 'هاتف التواصل', icon: Icons.phone, keyboardType: TextInputType.phone),
            if (isMissing) ...[
              AppTextField(controller: _c['last_known_place']!, label: 'آخر مكان شوهد فيه', icon: Icons.place),
              AppTextField(controller: _c['last_seen_date']!, label: 'تاريخ آخر مشاهدة YYYY-MM-DD', icon: Icons.event, required: false),
              AppTextField(controller: _c['relationship']!, label: 'صلة القرابة', icon: Icons.family_restroom),
            ] else ...[
              AppTextField(controller: _c['last_known_place']!, label: 'مكان العثور عليه', icon: Icons.place),
              AppTextField(controller: _c['current_location']!, label: 'مكان وجوده الحالي', icon: Icons.my_location),
              AppTextField(controller: _c['registered_by_name']!, label: 'اسم الشخص أو الجهة المسجلة', icon: Icons.business),
            ],
            DropdownButtonFormField<String>(
              initialValue: _priority,
              decoration: const InputDecoration(labelText: 'الأولوية', prefixIcon: Icon(Icons.priority_high)),
              items: const [
                DropdownMenuItem(value: 'low', child: Text('منخفضة')),
                DropdownMenuItem(value: 'normal', child: Text('عادية')),
                DropdownMenuItem(value: 'high', child: Text('عالية')),
              ],
              onChanged: (value) => setState(() => _priority = value ?? 'normal'),
            ),
            const SizedBox(height: 8),
            OutlinedButton.icon(
              onPressed: _pickImage,
              icon: const Icon(Icons.image),
              label: Text(_image == null ? 'إضافة صورة اختيارية' : 'تم اختيار الصورة'),
            ),
            const SizedBox(height: 12),
            FilledButton.icon(
              onPressed: _busy ? null : _submit,
              icon: _busy ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.send),
              label: Text(t(context, 'submit')),
            ),
          ],
        ),
      ),
    );
  }
}

class SearchScreen extends StatefulWidget {
  const SearchScreen({super.key});

  @override
  State<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> {
  final _name = TextEditingController();
  final _age = TextEditingController();
  final _city = TextEditingController();
  final _area = TextEditingController();
  String _type = '';
  Future<List<FamilyRequest>>? _results;

  void _search() {
    setState(() {
      _results = context.read<AppState>().api.search({
        if (_name.text.isNotEmpty) 'name': _name.text,
        if (_age.text.isNotEmpty) 'age': _age.text,
        if (_city.text.isNotEmpty) 'city': _city.text,
        if (_area.text.isNotEmpty) 'area': _area.text,
        if (_type.isNotEmpty) 'request_type': _type,
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(t(context, 'search'))),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          AppTextField(controller: _name, label: 'الاسم أو جزء منه', icon: Icons.search, required: false),
          Row(
            children: [
              Expanded(child: AppTextField(controller: _age, label: 'العمر', icon: Icons.cake, keyboardType: TextInputType.number, required: false)),
              const SizedBox(width: 8),
              Expanded(child: AppTextField(controller: _city, label: 'المدينة', icon: Icons.location_city, required: false)),
            ],
          ),
          AppTextField(controller: _area, label: 'المنطقة أو آخر مكان معروف', icon: Icons.map, required: false),
          SegmentedButton<String>(
            segments: const [
              ButtonSegment(value: '', label: Text('الكل'), icon: Icon(Icons.all_inclusive)),
              ButtonSegment(value: 'missing', label: Text('مفقود'), icon: Icon(Icons.person_search)),
              ButtonSegment(value: 'found', label: Text('موجود'), icon: Icon(Icons.add_location_alt)),
            ],
            selected: {_type},
            onSelectionChanged: (value) => setState(() => _type = value.first),
          ),
          const SizedBox(height: 12),
          FilledButton.icon(onPressed: _search, icon: const Icon(Icons.search), label: Text(t(context, 'search'))),
          const SizedBox(height: 12),
          if (_results != null)
            FutureBuilder<List<FamilyRequest>>(
              future: _results,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) return const LinearProgressIndicator();
                if (snapshot.hasError) return ErrorPanel(message: snapshot.error.toString());
                final items = snapshot.data ?? [];
                if (items.isEmpty) return EmptyPanel(message: t(context, 'noData'));
                return Column(children: items.map((item) => RequestCard(request: item, showMatch: true)).toList());
              },
            ),
        ],
      ),
    );
  }
}

class RequestDetailScreen extends StatefulWidget {
  const RequestDetailScreen({required this.id, super.key});

  final int id;

  @override
  State<RequestDetailScreen> createState() => _RequestDetailScreenState();
}

class _RequestDetailScreenState extends State<RequestDetailScreen> {
  late Future<FamilyRequest> _request;

  @override
  void initState() {
    super.initState();
    _request = context.read<AppState>().api.requestDetails(widget.id);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(t(context, 'details'))),
      body: FutureBuilder<FamilyRequest>(
        future: _request,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) return const Center(child: CircularProgressIndicator());
          if (snapshot.hasError) return ErrorPanel(message: snapshot.error.toString());
          final item = snapshot.data!;
          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              RequestCard(request: item),
              InfoTile(label: 'الوصف', value: item.description),
              InfoTile(label: 'الهاتف', value: item.contactPhone ?? 'مخفي لحين المراجعة'),
              FilledButton.icon(
                onPressed: () => showMatchSheet(context, matchedRequestId: item.id),
                icon: const Icon(Icons.handshake),
                label: Text(t(context, 'possibleMatch')),
              ),
            ],
          );
        },
      ),
    );
  }
}

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  late Future<List<NotificationItem>> _items;

  @override
  void initState() {
    super.initState();
    _items = context.read<AppState>().api.notifications();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(t(context, 'notifications'))),
      body: FutureBuilder<List<NotificationItem>>(
        future: _items,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) return const Center(child: CircularProgressIndicator());
          if (snapshot.hasError) return ErrorPanel(message: snapshot.error.toString());
          final items = snapshot.data ?? [];
          if (items.isEmpty) return EmptyPanel(message: t(context, 'noData'));
          return ListView(
            padding: const EdgeInsets.all(16),
            children: items.map((item) {
              return Card(
                child: ListTile(
                  leading: Icon(item.isRead ? Icons.mark_email_read : Icons.markunread),
                  title: Text(item.message),
                  subtitle: Text(item.createdAt),
                  onTap: () async {
                    await context.read<AppState>().api.markNotificationRead(item.id);
                    if (mounted) setState(() => _items = context.read<AppState>().api.notifications());
                  },
                ),
              );
            }).toList(),
          );
        },
      ),
    );
  }
}

class ProfileSettingsScreen extends StatelessWidget {
  const ProfileSettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final state = context.watch<AppState>();
    return Scaffold(
      appBar: AppBar(title: Text(t(context, 'profile'))),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (state.account != null)
            Card(
              child: ListTile(
                leading: const Icon(Icons.person),
                title: Text(state.account!.fullName),
                subtitle: Text('${state.account!.email} - ${state.account!.role}'),
              ),
            ),
          SwitchListTile(
            value: state.locale.languageCode == 'en',
            onChanged: (value) => state.setLocale(Locale(value ? 'en' : 'ar')),
            title: const Text('English'),
            secondary: const Icon(Icons.language),
          ),
          const Card(
            child: Padding(
              padding: EdgeInsets.all(16),
              child: Text('سياسة الخصوصية: لا نعرض رقم الهاتف الكامل في نتائج البحث، وتبقى بيانات التواصل خاضعة لمراجعة المسؤول.'),
            ),
          ),
          if (state.isAuthenticated)
            FilledButton.icon(
              onPressed: () async {
                await state.logout();
                if (context.mounted) Navigator.pushNamedAndRemoveUntil(context, '/home', (_) => false);
              },
              icon: const Icon(Icons.logout),
              label: Text(t(context, 'logout')),
            ),
        ],
      ),
    );
  }
}

class AdminScreen extends StatefulWidget {
  const AdminScreen({super.key});

  @override
  State<AdminScreen> createState() => _AdminScreenState();
}

class _AdminScreenState extends State<AdminScreen> {
  int _tab = 0;

  Future<Map<String, dynamic>> _load() {
    final api = context.read<AppState>().api;
    return switch (_tab) {
      0 => api.adminDashboard(),
      1 => api.adminUsers(),
      2 => api.adminRequests(),
      3 => api.adminMatches(),
      _ => api.adminReports(),
    };
  }

  @override
  Widget build(BuildContext context) {
    if (!context.watch<AppState>().isAdmin) {
      return const Scaffold(body: Center(child: Text('هذه الصفحة للمسؤول فقط.')));
    }
    return Scaffold(
      appBar: AppBar(title: Text(t(context, 'admin'))),
      body: Column(
        children: [
          NavigationBar(
            selectedIndex: _tab,
            onDestinationSelected: (value) => setState(() => _tab = value),
            destinations: const [
              NavigationDestination(icon: Icon(Icons.analytics), label: 'إحصاء'),
              NavigationDestination(icon: Icon(Icons.people), label: 'مستخدمون'),
              NavigationDestination(icon: Icon(Icons.assignment), label: 'بلاغات'),
              NavigationDestination(icon: Icon(Icons.handshake), label: 'تطابقات'),
              NavigationDestination(icon: Icon(Icons.report), label: 'تقارير'),
            ],
          ),
          Expanded(
            child: FutureBuilder<Map<String, dynamic>>(
              future: _load(),
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) return const Center(child: CircularProgressIndicator());
                if (snapshot.hasError) return ErrorPanel(message: snapshot.error.toString());
                return AdminPayloadView(tab: _tab, data: snapshot.data ?? {});
              },
            ),
          ),
        ],
      ),
    );
  }
}

class AdminPayloadView extends StatelessWidget {
  const AdminPayloadView({required this.tab, required this.data, super.key});

  final int tab;
  final Map<String, dynamic> data;

  @override
  Widget build(BuildContext context) {
    final items = switch (tab) {
      1 => data['users'] as List? ?? [],
      2 => data['requests'] as List? ?? [],
      3 => data['matches'] as List? ?? [],
      4 => data['reports'] as List? ?? [],
      _ => const [],
    };
    if (tab == 0) {
      final stats = data['stats'] as Map<String, dynamic>? ?? {};
      return ListView(
        padding: const EdgeInsets.all(16),
        children: stats.entries.map((e) => InfoTile(label: e.key, value: e.value.toString())).toList(),
      );
    }
    if (items.isEmpty) return EmptyPanel(message: t(context, 'noData'));
    return ListView(
      padding: const EdgeInsets.all(16),
      children: items.map((item) {
        final row = Map<String, dynamic>.from(item as Map);
        return Card(
          child: ListTile(
            title: Text(row['full_name']?.toString() ?? row['message']?.toString() ?? 'سجل #${row.values.first}'),
            subtitle: Text(jsonEncode(row)),
            trailing: tab == 1 || tab == 2 || tab == 3
                ? PopupMenuButton<String>(
                    onSelected: (status) async {
                      final api = context.read<AppState>().api;
                      if (tab == 1) await api.updateUserStatus(int.parse(row['account_id'].toString()), status);
                      if (tab == 2) await api.updateRequestStatus(int.parse(row['request_id'].toString()), status);
                      if (tab == 3) await api.updateMatchStatus(int.parse(row['match_id'].toString()), status);
                      if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('تم تحديث الحالة إلى $status')));
                    },
                    itemBuilder: (_) => const [
                      PopupMenuItem(value: 'active', child: Text('active')),
                      PopupMenuItem(value: 'approved', child: Text('approved')),
                      PopupMenuItem(value: 'rejected', child: Text('rejected')),
                      PopupMenuItem(value: 'resolved', child: Text('resolved')),
                    ],
                  )
                : null,
          ),
        );
      }).toList(),
    );
  }
}

class RequestCard extends StatelessWidget {
  const RequestCard({required this.request, this.showMatch = false, super.key});

  final FamilyRequest request;
  final bool showMatch;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: ListTile(
        leading: CircleAvatar(child: Text(request.matchPercent > 0 ? '${request.matchPercent.round()}%' : (request.type.isEmpty ? '?' : request.type.substring(0, 1)))),
        title: Text(request.fullName),
        subtitle: Text('${request.age} سنة - ${request.city} - ${request.status}'),
        isThreeLine: true,
        trailing: Wrap(
          spacing: 4,
          children: [
            IconButton(
              tooltip: t(context, 'details'),
              onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => RequestDetailScreen(id: request.id))),
              icon: const Icon(Icons.info_outline),
            ),
            if (showMatch)
              IconButton(
                tooltip: t(context, 'possibleMatch'),
                onPressed: () => showMatchSheet(context, matchedRequestId: request.id),
                icon: const Icon(Icons.handshake),
              ),
          ],
        ),
      ),
    );
  }
}

Future<void> showMatchSheet(BuildContext context, {required int matchedRequestId}) async {
  final myRequest = TextEditingController();
  final notes = TextEditingController();
  final phone = TextEditingController();
  await showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    builder: (sheetContext) => Padding(
      padding: EdgeInsets.only(
        left: 16,
        right: 16,
        top: 16,
        bottom: MediaQuery.of(sheetContext).viewInsets.bottom + 16,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(t(context, 'possibleMatch'), style: Theme.of(context).textTheme.titleLarge),
          AppTextField(controller: myRequest, label: 'رقم بلاغك', icon: Icons.confirmation_number, keyboardType: TextInputType.number),
          AppTextField(controller: notes, label: 'ملاحظات', icon: Icons.notes, maxLines: 3),
          AppTextField(controller: phone, label: 'هاتف التواصل', icon: Icons.phone, keyboardType: TextInputType.phone),
          FilledButton.icon(
            onPressed: () async {
              try {
                await context.read<AppState>().api.reportPossibleMatch(
                      requestId: int.parse(myRequest.text),
                      matchedRequestId: matchedRequestId,
                      notes: notes.text,
                      contactPhone: phone.text,
                    );
                if (context.mounted) Navigator.pop(sheetContext);
              } catch (error) {
                if (context.mounted) showError(context, error);
              }
            },
            icon: const Icon(Icons.send),
            label: Text(t(context, 'submit')),
          ),
        ],
      ),
    ),
  );
}

class AppTextField extends StatelessWidget {
  const AppTextField({
    required this.controller,
    required this.label,
    required this.icon,
    this.keyboardType,
    this.obscure = false,
    this.maxLines = 1,
    this.required = true,
    super.key,
  });

  final TextEditingController controller;
  final String label;
  final IconData icon;
  final TextInputType? keyboardType;
  final bool obscure;
  final int maxLines;
  final bool required;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: TextFormField(
        controller: controller,
        keyboardType: keyboardType,
        obscureText: obscure,
        maxLines: maxLines,
        decoration: InputDecoration(labelText: label, prefixIcon: Icon(icon), border: const OutlineInputBorder()),
        validator: required ? (value) => value == null || value.trim().isEmpty ? 'هذا الحقل مطلوب' : null : null,
      ),
    );
  }
}

class StatChip extends StatelessWidget {
  const StatChip({required this.label, required this.value, super.key});

  final String label;
  final int value;

  @override
  Widget build(BuildContext context) {
    return Chip(
      avatar: const Icon(Icons.insights, size: 18),
      label: Text('$label: $value'),
    );
  }
}

class InfoTile extends StatelessWidget {
  const InfoTile({required this.label, required this.value, super.key});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: ListTile(title: Text(label), subtitle: Text(value)),
    );
  }
}

class EmptyPanel extends StatelessWidget {
  const EmptyPanel({required this.message, super.key});

  final String message;

  @override
  Widget build(BuildContext context) => Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Text(message, textAlign: TextAlign.center),
        ),
      );
}

class ErrorPanel extends StatelessWidget {
  const ErrorPanel({required this.message, super.key});

  final String message;

  @override
  Widget build(BuildContext context) => Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Text('${t(context, 'noInternet')}\n$message', textAlign: TextAlign.center),
        ),
      );
}

void showError(BuildContext context, Object error) {
  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error.toString())));
}
