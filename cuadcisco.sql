-- phpMyAdmin SQL Dump
-- version 4.6.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 30-12-2016 a las 17:25:36
-- Versión del servidor: 5.5.51
-- Versión de PHP: 5.6.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cuadcisco`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `apps`
--

CREATE TABLE `apps` (
  `idApp` int(11) NOT NULL,
  `app_name` varchar(80) NOT NULL,
  `link` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `apps`
--

INSERT INTO `apps` (`idApp`, `app_name`, `link`) VALUES
(1, 'ASUR', 'https://10.1.106.31:8443/clarusipc/j_clarus_security_check'),
(2, 'BACHOCO', 'https://ucx-bachoco.vsys.com/clarusipc/j_clarus_security_check'),
(3, 'BANORTE', 'https://ucx-banorte.vsys.com/clarusipc/j_clarus_security_check'),
(4, 'BANSEFI', 'https://ucx-bansefi.vsys.com/clarusipc/j_clarus_security_check'),
(5, 'BANCOMER', 'https://ucx-bancomer.vsys.com/clarusipc/j_clarus_security_check'),
(6, 'CAPOME', 'https://ucx-capome.vsys.com/clarusipc/j_clarus_security_check'),
(7, 'IDEAL', 'https://ucx-ideal.vsys.com/clarusipc/j_clarus_security_check'),
(8, 'LALA', 'https://ucx-lala.vsys.com/clarusipc/j_clarus_security_check'),
(9, 'MBJ', 'https://ucx-mbj.vsys.com/clarusipc/j_clarus_security_check'),
(10, 'MIFEL', 'https://ucx-mifel.vsys.com/clarusipc/j_clarus_security_check'),
(11, 'SAT', 'https://ucx-sat.vsys.com/clarusipc/j_clarus_security_check'),
(12, 'VCB', 'https://ucx-vcb.vsys.com/clarusipc/j_clarus_security_check');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role` varchar(20) NOT NULL,
  `user_group` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `role`, `user_group`) VALUES
(1, 'User', 'Operator'),
(2, 'Provisioning', 'Administrator'),
(3, 'Auditor', 'Operator'),
(4, 'Root', 'Administrator');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `user_group` varchar(30) NOT NULL,
  `role` varchar(20) NOT NULL,
  `email` varchar(35) NOT NULL,
  `username` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `user_group`, `role`, `email`, `username`) VALUES
(2, 'Victor Valdez', '1', '1', 'vvaldez@vsys.com', 'vvaldez'),
(3, 'Rafael Acosta Lopez', '1', '1', '', 'vcbracosta'),
(4, 'Javier fernandez Rojas', '1', '1', '', 'vcbjfernandez'),
(5, 'Juan Carlos Perez Noriega', '1', '1', '', 'vcbjperez'),
(6, 'Juan Pablo Orozco', '1', '1', 'sistemas@vsys.com', 'jorozco'),
(7, 'Carlos José Gerardo Hernández Dueñas', '1', '1', '', 'asurchernandez'),
(8, 'Yalú Angélica Mondragón Méndez', '1', '1', '', 'asurymondragon'),
(9, 'Gabriel Pérez Romo', '1', '1', '', 'asurgperez'),
(10, 'Oswaldo de la Cruz Cedillo', '1', '1', '', 'asurodelacruz'),
(11, 'Asur', '1', '1', '', 'casur'),
(12, 'Rbachoco', '1', '1', '', 'rbachoco'),
(13, 'cbanorte', '1', '1', '', 'cbanorte'),
(14, 'rbansefi', '1', '1', '', 'rbansefi'),
(15, 'Reportes Bancomer', '1', '1', 'sistemas@vsys.com', 'rbancomer'),
(16, 'Reportes CAPOME', '1', '1', 'sistemas@vsys.com', 'rcapome'),
(17, 'rideal', '1', '1', '', 'rideal'),
(18, 'rlala', '1', '1', '', 'rlala'),
(19, 'rmbj', '1', '1', '', 'rmbj'),
(20, 'rmifel', '1', '1', '', 'rmifel'),
(21, 'rsat', '1', '1', '', 'rsat'),
(22, 'rvector', '1', '1', '', 'rvector'),
(23, 'Tlehuek Tapia', '2', '2', 'sistemas@vsys.com', 'ttapia'),
(24, 'Administrador AUTH UCX', '2', '2', 'sistemas@vsys.com', 'aauth');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usersapp`
--

CREATE TABLE `usersapp` (
  `id` int(11) NOT NULL,
  `user` varchar(15) NOT NULL,
  `app` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `usersapp`
--

INSERT INTO `usersapp` (`id`, `user`, `app`) VALUES
(1, 'vvaldez', '1'),
(2, 'vvaldez', '2'),
(3, 'vvaldez', '3'),
(4, 'vvaldez', '4'),
(5, 'vvaldez', '5'),
(6, 'vvaldez', '6'),
(7, 'vvaldez', '7'),
(8, 'vvaldez', '8'),
(9, 'vvaldez', '9'),
(10, 'vvaldez', '10'),
(11, 'vvaldez', '11'),
(12, 'vvaldez', '12'),
(13, 'vcbracosta', '12'),
(14, 'vcbjfernandez', '12'),
(15, 'vcbevelazquez', '12'),
(16, 'vcbjperez', '12'),
(17, 'jorozco', ''),
(18, 'asurchernandez', '1'),
(19, 'asurymondragon', '1'),
(20, 'asurgperez', '1'),
(21, 'asurodelacruz', '1'),
(22, 'casur', '1'),
(23, 'rbachoco', '2'),
(24, 'cbanorte', '3'),
(25, 'rbansefi', '4'),
(26, 'rbancomer', '5'),
(27, 'rcapome', '6'),
(28, 'rideal', '7'),
(29, 'rlala', '8'),
(30, 'rmbj', '9'),
(31, 'rmifel', '10'),
(32, 'rsat', '11'),
(33, 'rvector', '12'),
(34, 'ttapia', '2'),
(35, 'ttapia', '3'),
(36, 'ttapia', '4'),
(37, 'ttapia', '5'),
(38, 'ttapia', '6'),
(39, 'ttapia', '7'),
(40, 'ttapia', '8'),
(41, 'ttapia', '9'),
(42, 'ttapia', '10'),
(43, 'ttapia', '11'),
(44, 'ttapia', '12'),
(45, 'aauth', '2'),
(46, 'aauth', '3'),
(47, 'aauth', '4'),
(48, 'aauth', '5'),
(49, 'aauth', '6'),
(50, 'aauth', '7'),
(51, 'aauth', '8'),
(52, 'aauth', '9'),
(53, 'aauth', '10'),
(54, 'aauth', '11'),
(55, 'aauth', '12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_groups`
--

CREATE TABLE `user_groups` (
  `id_user_group` int(11) NOT NULL,
  `user_group` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `user_groups`
--

INSERT INTO `user_groups` (`id_user_group`, `user_group`) VALUES
(1, 'Operators'),
(2, 'Administradors');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `apps`
--
ALTER TABLE `apps`
  ADD PRIMARY KEY (`idApp`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usersapp`
--
ALTER TABLE `usersapp`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `user_groups`
--
ALTER TABLE `user_groups`
  ADD PRIMARY KEY (`id_user_group`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `apps`
--
ALTER TABLE `apps`
  MODIFY `idApp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
--
-- AUTO_INCREMENT de la tabla `usersapp`
--
ALTER TABLE `usersapp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;
--
-- AUTO_INCREMENT de la tabla `user_groups`
--
ALTER TABLE `user_groups`
  MODIFY `id_user_group` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
