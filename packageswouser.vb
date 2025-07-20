Imports System.Drawing
Imports System.Windows.Forms

Public Class PackagesWithoutUserForm
    Inherits Form

    Private components As System.ComponentModel.IContainer

    ' Navigation controls
    Private pnlNavigation As Panel
    Private lblLogo As Label
    Private btnHome As Button
    Private btnPackages As Button
    Private btnAboutUs As Button

    ' Package cards
    Private pnlDomestic As Panel
    Private pnlInternational As Panel
    Private pnlFreediving As Panel
    Private lblDomestic As Label
    Private lblInternational As Label
    Private lblFreediving As Label

    ' Background panel
    Private pnlBackground As Panel

    Public Sub New()
        InitializeComponent()
        SetupForm()
        SetupNavigation()
        SetupPackageCards()
    End Sub

    Private Sub InitializeComponent()
        Me.pnlNavigation = New System.Windows.Forms.Panel()
        Me.lblLogo = New System.Windows.Forms.Label()
        Me.btnHome = New System.Windows.Forms.Button()
        Me.btnPackages = New System.Windows.Forms.Button()
        Me.btnAboutUs = New System.Windows.Forms.Button()
        Me.pnlBackground = New System.Windows.Forms.Panel()
        Me.pnlDomestic = New System.Windows.Forms.Panel()
        Me.pnlInternational = New System.Windows.Forms.Panel()
        Me.pnlFreediving = New System.Windows.Forms.Panel()
        Me.lblDomestic = New System.Windows.Forms.Label()
        Me.lblInternational = New System.Windows.Forms.Label()
        Me.lblFreediving = New System.Windows.Forms.Label()
        Me.SuspendLayout()
        '
        'pnlNavigation
        '
        Me.pnlNavigation.Location = New System.Drawing.Point(0, 0)
        Me.pnlNavigation.Name = "pnlNavigation"
        Me.pnlNavigation.Size = New System.Drawing.Size(200, 100)
        Me.pnlNavigation.TabIndex = 0
        '
        'lblLogo
        '
        Me.lblLogo.Location = New System.Drawing.Point(0, 0)
        Me.lblLogo.Name = "lblLogo"
        Me.lblLogo.Size = New System.Drawing.Size(100, 23)
        Me.lblLogo.TabIndex = 0
        '
        'btnHome
        '
        Me.btnHome.Location = New System.Drawing.Point(0, 0)
        Me.btnHome.Name = "btnHome"
        Me.btnHome.Size = New System.Drawing.Size(75, 23)
        Me.btnHome.TabIndex = 0
        '
        'btnPackages
        '
        Me.btnPackages.Location = New System.Drawing.Point(0, 0)
        Me.btnPackages.Name = "btnPackages"
        Me.btnPackages.Size = New System.Drawing.Size(75, 23)
        Me.btnPackages.TabIndex = 0
        '
        'btnAboutUs
        '
        Me.btnAboutUs.Location = New System.Drawing.Point(0, 0)
        Me.btnAboutUs.Name = "btnAboutUs"
        Me.btnAboutUs.Size = New System.Drawing.Size(75, 23)
        Me.btnAboutUs.TabIndex = 0
        '
        'pnlBackground
        '
        Me.pnlBackground.Location = New System.Drawing.Point(0, 0)
        Me.pnlBackground.Name = "pnlBackground"
        Me.pnlBackground.Size = New System.Drawing.Size(200, 100)
        Me.pnlBackground.TabIndex = 1
        '
        'pnlDomestic
        '
        Me.pnlDomestic.Location = New System.Drawing.Point(0, 0)
        Me.pnlDomestic.Name = "pnlDomestic"
        Me.pnlDomestic.Size = New System.Drawing.Size(200, 100)
        Me.pnlDomestic.TabIndex = 0
        '
        'pnlInternational
        '
        Me.pnlInternational.Location = New System.Drawing.Point(0, 0)
        Me.pnlInternational.Name = "pnlInternational"
        Me.pnlInternational.Size = New System.Drawing.Size(200, 100)
        Me.pnlInternational.TabIndex = 0
        '
        'pnlFreediving
        '
        Me.pnlFreediving.Location = New System.Drawing.Point(0, 0)
        Me.pnlFreediving.Name = "pnlFreediving"
        Me.pnlFreediving.Size = New System.Drawing.Size(200, 100)
        Me.pnlFreediving.TabIndex = 0
        '
        'lblDomestic
        '
        Me.lblDomestic.Location = New System.Drawing.Point(0, 0)
        Me.lblDomestic.Name = "lblDomestic"
        Me.lblDomestic.Size = New System.Drawing.Size(100, 23)
        Me.lblDomestic.TabIndex = 0
        '
        'lblInternational
        '
        Me.lblInternational.Location = New System.Drawing.Point(0, 0)
        Me.lblInternational.Name = "lblInternational"
        Me.lblInternational.Size = New System.Drawing.Size(100, 23)
        Me.lblInternational.TabIndex = 0
        '
        'lblFreediving
        '
        Me.lblFreediving.Location = New System.Drawing.Point(0, 0)
        Me.lblFreediving.Name = "lblFreediving"
        Me.lblFreediving.Size = New System.Drawing.Size(100, 23)
        Me.lblFreediving.TabIndex = 0
        '
        'PackagesWithoutUserForm
        '
        Me.BackColor = System.Drawing.Color.FromArgb(CType(CType(26, Byte), Integer), CType(CType(188, Byte), Integer), CType(CType(156, Byte), Integer))
        Me.ClientSize = New System.Drawing.Size(1382, 753)
        Me.Controls.Add(Me.pnlNavigation)
        Me.Controls.Add(Me.pnlBackground)
        Me.Name = "PackagesWithoutUserForm"
        Me.StartPosition = System.Windows.Forms.FormStartPosition.CenterScreen
        Me.Text = "LakbayPH - Travel Packages"
        Me.WindowState = System.Windows.Forms.FormWindowState.Maximized
        Me.ResumeLayout(False)

    End Sub

    Private Sub SetupForm()
        ' Set form background to tropical ocean color
        Me.BackColor = Color.FromArgb(26, 188, 156)
        AddHandler Me.Resize, AddressOf Form_Resize
    End Sub

    Private Sub Form_Resize(sender As Object, e As EventArgs)
        ' Reposition elements on resize
        If Me.WindowState = FormWindowState.Maximized Then
            ResizeComponents()
        End If
    End Sub

    Private Sub ResizeComponents()
        ' Adjust component sizes based on form size
        If pnlBackground IsNot Nothing Then
            pnlBackground.Size = New Size(Me.ClientSize.Width, Me.ClientSize.Height - 70)
            RepositionPackageCards()
        End If
    End Sub

    Private Sub SetupNavigation()
        ' Navigation Panel
        With Me.pnlNavigation
            .Location = New Point(0, 0)
            .Size = New Size(Me.Width, 70)
            .BackColor = Color.White
            .Dock = DockStyle.Top
            .BorderStyle = BorderStyle.None
        End With

        ' Logo
        With Me.lblLogo
            .Text = "🌊 LakbayPH" & vbCrLf & "Travel & Tours"
            .Font = New Font("Segoe UI", 12, FontStyle.Bold)
            .ForeColor = Color.FromArgb(44, 95, 97)
            .Location = New Point(20, 10)
            .Size = New Size(200, 50)
            .BackColor = Color.Transparent
            .TextAlign = ContentAlignment.MiddleLeft
        End With

        ' Home Button
        With Me.btnHome
            .Text = "Home"
            .Font = New Font("Segoe UI", 11)
            .Location = New Point(Me.Width - 300, 20)
            .Size = New Size(80, 30)
            .BackColor = Color.Transparent
            .ForeColor = Color.FromArgb(100, 100, 100)
            .FlatStyle = FlatStyle.Flat
            .FlatAppearance.BorderSize = 0
            .FlatAppearance.MouseOverBackColor = Color.FromArgb(240, 240, 240)
            .Cursor = Cursors.Hand
            AddHandler .Click, AddressOf BtnHome_Click
        End With

        ' Packages Button (Active)
        With Me.btnPackages
            .Text = "Packages"
            .Font = New Font("Segoe UI", 11, FontStyle.Bold)
            .Location = New Point(Me.Width - 200, 20)
            .Size = New Size(80, 30)
            .BackColor = Color.FromArgb(100, 200, 255)
            .ForeColor = Color.White
            .FlatStyle = FlatStyle.Flat
            .FlatAppearance.BorderSize = 0
            .Cursor = Cursors.Hand
        End With

        ' About Us Button
        With Me.btnAboutUs
            .Text = "About Us"
            .Font = New Font("Segoe UI", 11)
            .Location = New Point(Me.Width - 100, 20)
            .Size = New Size(80, 30)
            .BackColor = Color.Transparent
            .ForeColor = Color.FromArgb(100, 100, 100)
            .FlatStyle = FlatStyle.Flat
            .FlatAppearance.BorderSize = 0
            .FlatAppearance.MouseOverBackColor = Color.FromArgb(240, 240, 240)
            .Cursor = Cursors.Hand
            AddHandler .Click, AddressOf BtnAboutUs_Click
        End With

        ' Add navigation controls to panel
        Me.pnlNavigation.Controls.Add(Me.lblLogo)
        Me.pnlNavigation.Controls.Add(Me.btnHome)
        Me.pnlNavigation.Controls.Add(Me.btnPackages)
        Me.pnlNavigation.Controls.Add(Me.btnAboutUs)
    End Sub

    Private Sub SetupPackageCards()
        ' Background Panel - tropical ocean color
        With Me.pnlBackground
            .Location = New Point(0, 70)
            .Size = New Size(Me.Width, Me.Height - 70)
            .BackColor = Color.FromArgb(26, 188, 156)
        End With

        ' Calculate card positions
        Dim cardWidth As Integer = 350
        Dim cardHeight As Integer = 500
        Dim spacing As Integer = 50
        Dim startX As Integer = (Me.Width - (3 * cardWidth + 2 * spacing)) \ 2
        Dim startY As Integer = 100

        ' Domestic Package Card - tropical sunset colors
        With Me.pnlDomestic
            .Location = New Point(startX, startY)
            .Size = New Size(cardWidth, cardHeight)
            .BackColor = Color.FromArgb(255, 180, 180) ' Light coral/sunset color
            .BorderStyle = BorderStyle.FixedSingle
            .Cursor = Cursors.Hand
            AddHandler .Click, AddressOf PnlDomestic_Click
            AddHandler .MouseEnter, AddressOf PackageCard_MouseEnter
            AddHandler .MouseLeave, AddressOf PackageCard_MouseLeave
        End With

        ' Domestic Label
        With Me.lblDomestic
            .Text = "DOMESTIC"
            .Font = New Font("Segoe UI", 24, FontStyle.Bold)
            .ForeColor = Color.Black
            .Location = New Point(30, 350)
            .Size = New Size(290, 50)
            .BackColor = Color.Transparent
            .TextAlign = ContentAlignment.MiddleLeft
        End With

        ' International Package Card - light blue/gray colors
        With Me.pnlInternational
            .Location = New Point(startX + cardWidth + spacing, startY)
            .Size = New Size(cardWidth, cardHeight)
            .BackColor = Color.FromArgb(220, 220, 240) ' Light blue-gray
            .BorderStyle = BorderStyle.FixedSingle
            .Cursor = Cursors.Hand
            AddHandler .Click, AddressOf PnlInternational_Click
            AddHandler .MouseEnter, AddressOf PackageCard_MouseEnter
            AddHandler .MouseLeave, AddressOf PackageCard_MouseLeave
        End With

        ' International Label
        With Me.lblInternational
            .Text = "INTERNATIONAL"
            .Font = New Font("Segoe UI", 24, FontStyle.Bold)
            .ForeColor = Color.FromArgb(50, 50, 50)
            .Location = New Point(30, 350)
            .Size = New Size(290, 50)
            .BackColor = Color.Transparent
            .TextAlign = ContentAlignment.MiddleLeft
        End With

        ' Freediving Package Card - deep ocean blue
        With Me.pnlFreediving
            .Location = New Point(startX + 2 * (cardWidth + spacing), startY)
            .Size = New Size(cardWidth, cardHeight)
            .BackColor = Color.FromArgb(70, 130, 180) ' Steel blue/ocean color
            .BorderStyle = BorderStyle.FixedSingle
            .Cursor = Cursors.Hand
            AddHandler .Click, AddressOf PnlFreediving_Click
            AddHandler .MouseEnter, AddressOf PackageCard_MouseEnter
            AddHandler .MouseLeave, AddressOf PackageCard_MouseLeave
        End With

        ' Freediving Label
        With Me.lblFreediving
            .Text = "FREEDIVING"
            .Font = New Font("Segoe UI", 24, FontStyle.Bold)
            .ForeColor = Color.White ' White text on dark background
            .Location = New Point(30, 350)
            .Size = New Size(290, 50)
            .BackColor = Color.Transparent
            .TextAlign = ContentAlignment.MiddleLeft
        End With

        ' Add cards to background panel
        Me.pnlBackground.Controls.Add(Me.pnlDomestic)
        Me.pnlBackground.Controls.Add(Me.pnlInternational)
        Me.pnlBackground.Controls.Add(Me.pnlFreediving)
        Me.pnlDomestic.Controls.Add(Me.lblDomestic)
        Me.pnlInternational.Controls.Add(Me.lblInternational)
        Me.pnlFreediving.Controls.Add(Me.lblFreediving)
    End Sub

    Private Sub RepositionPackageCards()
        Dim cardWidth As Integer = 350
        Dim cardHeight As Integer = 500
        Dim spacing As Integer = 50
        Dim startX As Integer = (Me.ClientSize.Width - (3 * cardWidth + 2 * spacing)) \ 2
        Dim startY As Integer = 100

        pnlDomestic.Location = New Point(startX, startY)
        pnlInternational.Location = New Point(startX + cardWidth + spacing, startY)
        pnlFreediving.Location = New Point(startX + 2 * (cardWidth + spacing), startY)
    End Sub

    ' Event handlers for card interactions
    Private Sub PackageCard_MouseEnter(sender As Object, e As EventArgs)
        Dim panel As Panel = DirectCast(sender, Panel)
        panel.Location = New Point(panel.Location.X, panel.Location.Y - 10)

        ' Change color on hover for visual feedback
        If panel Is pnlDomestic Then
            panel.BackColor = Color.FromArgb(255, 200, 200) ' Lighter coral
        ElseIf panel Is pnlInternational Then
            panel.BackColor = Color.FromArgb(240, 240, 250) ' Lighter blue-gray
        ElseIf panel Is pnlFreediving Then
            panel.BackColor = Color.FromArgb(90, 150, 200) ' Lighter ocean blue
        End If
    End Sub

    Private Sub PackageCard_MouseLeave(sender As Object, e As EventArgs)
        Dim panel As Panel = DirectCast(sender, Panel)
        panel.Location = New Point(panel.Location.X, panel.Location.Y + 10)

        ' Restore original color
        If panel Is pnlDomestic Then
            panel.BackColor = Color.FromArgb(255, 180, 180) ' Original coral
        ElseIf panel Is pnlInternational Then
            panel.BackColor = Color.FromArgb(220, 220, 240) ' Original blue-gray
        ElseIf panel Is pnlFreediving Then
            panel.BackColor = Color.FromArgb(70, 130, 180) ' Original ocean blue
        End If
    End Sub

    ' Navigation event handlers
    Private Sub BtnHome_Click(sender As Object, e As EventArgs)
        ' Close this form
        Me.Close()
    End Sub

    Private Sub BtnAboutUs_Click(sender As Object, e As EventArgs)
        Dim aboutUsForm As New AboutUsForm()
        aboutUsForm.Show()
    End Sub

    ' Package card event handlers
    Private Sub PnlDomestic_Click(sender As Object, e As EventArgs)
        MessageBox.Show("Opening Domestic Packages..." & vbCrLf & "Explore beautiful destinations within the Philippines!", "Domestic Packages", MessageBoxButtons.OK, MessageBoxIcon.Information)
    End Sub

    Private Sub PnlInternational_Click(sender As Object, e As EventArgs)
        MessageBox.Show("Opening International Packages..." & vbCrLf & "Discover amazing destinations around the world!", "International Packages", MessageBoxButtons.OK, MessageBoxIcon.Information)
    End Sub

    Private Sub PnlFreediving_Click(sender As Object, e As EventArgs)
        MessageBox.Show("Opening Freediving Packages..." & vbCrLf & "Dive into underwater adventures!", "Freediving Packages", MessageBoxButtons.OK, MessageBoxIcon.Information)
    End Sub

    Protected Overrides Sub Dispose(disposing As Boolean)
        If disposing AndAlso (components IsNot Nothing) Then
            components.Dispose()
        End If
        MyBase.Dispose(disposing)
    End Sub

    Private Sub PackagesWithoutUserForm_Load(sender As Object, e As EventArgs) Handles MyBase.Load

    End Sub
End Class

' Module to run the packages form
Module PackagesPrograms
    Sub Main()
        Application.EnableVisualStyles()
        Application.SetCompatibleTextRenderingDefault(False)
        Application.Run(New PackagesWithoutUserForm())
    End Sub
End Module